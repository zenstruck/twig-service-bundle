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

use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zenstruck\Twig\Service\TwigServiceExtension;
use Zenstruck\Twig\Service\TwigServiceRuntime;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ZenstruckTwigServiceBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
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
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return null;
    }
}
