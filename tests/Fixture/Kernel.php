<?php

namespace Zenstruck\Twig\Tests\Fixture;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
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

        $a = $c->register(ServiceA::class)->setAutoconfigured(true)->setAutowired(true);
        $b = $c->register(ServiceB::class)->setAutoconfigured(true)->setAutowired(true);

        if (\PHP_VERSION_ID < 80000 || self::VERSION_ID < 50300) {
            $a->addTag('twig.service', ['alias' => 'service-a']);
            $b->addTag('twig.service', ['alias' => 'service-b']);
        }
    }

    protected function configureRoutes($routes): void
    {
    }
}
