<?php

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
        if (\method_exists($container, 'registerAttributeForAutoconfiguration')) {
            $container->registerAttributeForAutoconfiguration(
                AsTwigService::class,
                static function(ChildDefinition $definition, AsTwigService $attribute) {
                    $definition->addTag('twig.service', ['alias' => $attribute->alias]);
                }
            );
        }

        $container->register('zenstruck.twig.service_extension', TwigServiceExtension::class)
            ->addTag('twig.extension')
        ;
        $container->register('zenstruck.twig.service_runtime', TwigServiceRuntime::class)
            ->setArgument(0, new ServiceLocatorArgument(new TaggedIteratorArgument('twig.service', 'alias', null, true)))
            ->addTag('twig.runtime')
        ;
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return null;
    }
}
