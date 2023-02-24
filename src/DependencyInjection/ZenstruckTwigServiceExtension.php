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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Zenstruck\Twig\AsTwigService;
use Zenstruck\Twig\Service\TwigFunctionRuntime;
use Zenstruck\Twig\Service\TwigServiceExtension;
use Zenstruck\Twig\Service\TwigServiceRuntime;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ZenstruckTwigServiceExtension extends ConfigurableExtension implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('zenstruck_twig_service');

        $builder->getRootNode() // @phpstan-ignore-line
            ->children()
                ->arrayNode('functions')
                    ->info('Callables to make available with fn() twig function/filter')
                    ->example(['strlen', 'alias' => ['Some\Class', 'somePublicStaticMethod']])
                    ->variablePrototype()
                        ->validate()
                            ->ifTrue(fn($v) => \is_object($v) || \is_object($v[0] ?? null))
                            ->thenInvalid('Callable objects are not supported.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface // @phpstan-ignore-line
    {
        return $this;
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void // @phpstan-ignore-line
    {
        $functions = [];

        foreach ($mergedConfig['functions'] as $key => $value) {
            if (!\is_numeric($key)) {
                $functions[$key] = $value;

                continue;
            }

            if (\is_string($value) && \is_callable($value)) {
                $functions[(new \ReflectionFunction($value))->getShortName()] = $value;
            }

            $functions[$value[1] ?? throw new \LogicException('Invalid callable.')] = $value;
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
            ->addArgument(new ServiceLocatorArgument(new TaggedIteratorArgument('twig.service_method', 'alias', needsIndexes: true)))
            ->addTag('twig.runtime')
        ;
    }
}
