<?php

/*
 * This file is part of the zenstruck/twig-service-bundle package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Twig\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Zenstruck\Twig\AsTwigFunction;
use Zenstruck\Twig\AsTwigService;
use Zenstruck\Twig\Command\ListCommand;
use Zenstruck\Twig\Service\InvokableCallable;
use Zenstruck\Twig\Service\InvokableServiceMethod;
use Zenstruck\Twig\Service\TwigFunctionRuntime;
use Zenstruck\Twig\Service\TwigServiceExtension;
use Zenstruck\Twig\Service\TwigServiceRuntime;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 *
 * @phpstan-type Function = callable-string|(array{0:class-string,1:string}&callable)|array{0:string,1:string}
 * @phpstan-type FunctionContext = array{0:Function,1:mixed}
 */
final class ZenstruckTwigServiceExtension extends ConfigurableExtension implements ConfigurationInterface, CompilerPassInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('zenstruck_twig_service');

        $builder->getRootNode() // @phpstan-ignore-line
            ->children()
                ->arrayNode('functions')
                    ->info('Callables to make available with fn() twig function/filter')
                    ->example([
                        '0' => 'my_function',
                        'alias1' => ['Some\Class', 'somePublicStaticMethod'],
                        'alias2' => ['callable' => ['service_id', 'someMethod'], 'on_exception_return' => null],
                    ])
                    ->useAttributeAsKey('alias')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(fn($v) => ['callable' => $v])
                        ->end()
                        ->beforeNormalization()
                            ->ifTrue(fn($v) => \is_array($v) && array_is_list($v))
                            ->then(fn($v) => ['callable' => $v])
                        ->end()
                        ->children()
                            ->variableNode('callable')
                                ->info('{function name}, [{class name}, {static method}], [{service id}, {method}]')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->validate()
                                    ->ifTrue(fn($v) => \is_object($v) || \is_object($v[0] ?? null))
                                    ->thenInvalid('Callable objects are not supported.')
                                ->end()
                            ->end()
                            ->variableNode('on_exception_return')
                                ->info('The default value to return if the function throws an exception')
                                ->defaultValue(AsTwigFunction::THROW)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }

    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition('parameter_bag')
            ->addTag('twig.service', ['alias' => TwigServiceRuntime::PARAMETER_BAG])
        ;

        /** @var array<string,FunctionContext> $configuredFunctions */
        $configuredFunctions = $container->getParameter('zenstruck_twig_service.functions');

        $container->getParameterBag()->remove('zenstruck_twig_service.functions');

        $functions = [];

        foreach ($configuredFunctions as $alias => $context) {
            $functions = self::addToFunctions($functions, $alias, $context[0], $context[1]);
        }

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->hasTag('container.excluded')) {
                continue;
            }

            if (!$class = $definition->getClass()) {
                continue;
            }

            if (!\class_exists($class)) {
                continue;
            }

            foreach ((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if (!$attribute = $method->getAttributes(AsTwigFunction::class)[0] ?? null) {
                    continue;
                }

                $attribute = $attribute->newInstance();

                /** @var AsTwigFunction $attribute */
                $functions = self::addToFunctions(
                    $functions,
                    $attribute->alias ?? $method->name,
                    [$method->isStatic() ? $class : $id, $method->name],
                    $attribute->onExceptionReturn
                );
            }
        }

        foreach (\get_defined_functions()['user'] as $function) {
            $ref = new \ReflectionFunction($function);

            if (!$attribute = $ref->getAttributes(AsTwigFunction::class)[0] ?? null) {
                continue;
            }

            $attribute = $attribute->newInstance();

            /** @var AsTwigFunction $attribute */
            $functions = self::addToFunctions(
                $functions,
                $attribute->alias ?? $ref->getShortName(),
                $ref->name, // @phpstan-ignore-line
                $attribute->onExceptionReturn
            );
        }

        foreach ($functions as $alias => $functionContext) {
            $definition = $container->register('.zenstruck_twig_service.function.'.$alias)
                ->addArgument($functionContext[1])
                ->addTag('twig.function', ['alias' => $alias])
            ;

            if (\is_callable($functionContext[0])) {
                $definition->setClass(InvokableCallable::class)->addArgument($functionContext[0]);

                continue;
            }

            $definition
                ->setClass(InvokableServiceMethod::class)
                ->addArgument(new Reference($functionContext[0][0]))
                ->addArgument($functionContext[0][1])
            ;
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface // @phpstan-ignore-line
    {
        return $this;
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void // @phpstan-ignore-line
    {
        $functions = [];

        foreach ($mergedConfig['functions'] as $key => $value) {
            $alias = match (true) {
                !\is_numeric($key) => $key,
                \is_string($value['callable']) => (new \ReflectionFunction($value['callable']))->getShortName(),
                default => $value['callable'][1] ?? throw new \LogicException('Invalid callable.'),
            };

            $functions[$alias] = [$value['callable'], $value['on_exception_return']];
        }

        $container->setParameter('zenstruck_twig_service.functions', $functions);

        $container->registerAttributeForAutoconfiguration(
            AsTwigService::class,
            static function(ChildDefinition $definition, AsTwigService $attribute) {
                $definition->addTag('twig.service', ['alias' => $attribute->alias]);
            }
        );

        $container->register('.zenstruck.twig.service_extension', TwigServiceExtension::class)
            ->addTag('twig.extension')
        ;
        $container->register('.zenstruck.twig.service_runtime', TwigServiceRuntime::class)
            ->addArgument(new ServiceLocatorArgument(new TaggedIteratorArgument('twig.service', 'alias', needsIndexes: true)))
            ->addTag('twig.runtime')
        ;
        $container->register('.zenstruck.twig.function_runtime', TwigFunctionRuntime::class)
            ->addArgument(new ServiceLocatorArgument(new TaggedIteratorArgument('twig.function', 'alias', needsIndexes: true)))
            ->addTag('twig.runtime')
        ;

        if ($container->getParameter('kernel.debug')) {
            $container->register('.zenstruck.twig.list_command', ListCommand::class)
                ->setArguments([
                    new Reference('.zenstruck.twig.service_runtime'), new Reference('.zenstruck.twig.function_runtime'),
                ])
                ->addTag('console.command')
            ;
        }
    }

    /**
     * @param array<string,FunctionContext> $functions
     * @param Function                      $what
     *
     * @return array<string,FunctionContext>
     */
    private static function addToFunctions(array $functions, string $alias, string|array $what, mixed $onExceptionReturn): array
    {
        if (isset($functions[$alias])) {
            throw new LogicException(\sprintf('The function alias "%s" is already being used for "%s".', $alias, \is_string($functions[$alias][1]) ? $functions[$alias][1] : \implode('::', $functions[$alias][1])));
        }

        $functions[$alias] = [$what, $onExceptionReturn];

        return $functions;
    }
}
