<?php

/*
 * This file is part of the zenstruck/twig-service-bundle package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Twig\Tests\Fixture;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Zenstruck\Twig\ZenstruckTwigServiceBundle;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();
        yield new ZenstruckTwigServiceBundle();
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'secret' => 'S3CRET',
            'test' => true,
            'router' => ['utf8' => true],
            'secrets' => false,
        ]);
        $c->loadFromExtension('twig', [
            'default_path' => '%kernel.project_dir%/tests/Fixture/templates',
        ]);
        $c->loadFromExtension('zenstruck_twig_service', [
            'functions' => [
                'strlen',
                'trimalias' => 'trim',
                [SomeClass::class, 'someMethod1'],
                'some_method_2' => [SomeClass::class, 'someMethod2'],
                [ServiceD::class, 'serviceMethod1'],
                'service_method_2' => [ServiceD::class, 'serviceMethod2'],
                'router' => [
                    'callable' => ['router', 'generate'],
                    'on_exception_return' => 'error!',
                ],
            ],
        ]);

        $c->setParameter('foo', 'bar');
        $c->register(ServiceA::class)->setAutoconfigured(true)->setAutowired(true);
        $c->register(ServiceB::class)->setAutoconfigured(true)->setAutowired(true);
        $c->register(ServiceC::class)->setAutoconfigured(true)->setAutowired(true);
        $c->register(ServiceD::class)->setAutoconfigured(true)->setAutowired(true);
        $c->register(InvalidService::class);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->add('route', '/some/path');
    }
}
