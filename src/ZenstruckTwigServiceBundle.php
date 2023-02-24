<?php

/*
 * This file is part of the zenstruck/twig-service-bundle package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Twig;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zenstruck\Twig\Service\TwigFunctionRuntime;
use Zenstruck\Twig\Service\TwigServiceRuntime;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @phpstan-import-type Function from TwigFunctionRuntime
 */
final class ZenstruckTwigServiceBundle extends Bundle implements CompilerPassInterface
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this);
    }

    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition('parameter_bag')
            ->addTag('twig.service', ['alias' => TwigServiceRuntime::PARAMETER_BAG])
        ;

        /** @var array<string,Function> $userFunctions */
        $userFunctions = $container->getParameter('zenstruck_twig_service.functions');

        $container->getParameterBag()->remove('zenstruck_twig_service.functions');

        foreach ($container->getDefinitions() as $id => $definition) {
            if (!$class = $definition->getClass()) {
                continue;
            }

            if (!\class_exists($class)) {
                continue;
            }

            $addTag = false;

            foreach ((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if (!$attribute = $method->getAttributes(AsTwigFunction::class)[0] ?? null) {
                    continue;
                }

                $alias = $attribute->newInstance()->alias ?? $method->name;

                if (isset($userFunctions[$alias])) {
                    throw new LogicException(\sprintf('The function alias "%s" is already being used for "%s".', $alias, \is_string($userFunctions[$alias]) ? $userFunctions[$alias] : \implode('::', $userFunctions[$alias])));
                }

                if ($method->isStatic()) {
                    $userFunctions[$alias] = [$class, $method->name];

                    continue;
                }

                $addTag = true;
                $userFunctions[$alias] = [$id, $method->name];
            }

            if ($addTag) {
                $definition->addTag('twig.service_method', ['alias' => $id]);
            }
        }

        foreach (\get_defined_functions()['user'] as $function) {
            $ref = new \ReflectionFunction($function);

            if (!$attribute = $ref->getAttributes(AsTwigFunction::class)[0] ?? null) {
                continue;
            }

            $alias = $attribute->newInstance()->alias ?? $ref->getShortName();

            if (isset($userFunctions[$alias])) {
                throw new LogicException(\sprintf('The function alias "%s" is already being used for "%s".', $alias, \is_string($userFunctions[$alias]) ? $userFunctions[$alias] : \implode('::', $userFunctions[$alias])));
            }

            $userFunctions[$alias] = $ref->name;
        }

        $container->getDefinition('.zenstruck.twig.function_runtime')
            ->addArgument($userFunctions)
        ;
    }
}
