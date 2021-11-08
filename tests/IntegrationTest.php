<?php

namespace Zenstruck\Twig\Tests;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Error\RuntimeError;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IntegrationTest extends KernelTestCase
{
    /**
     * @test
     */
    public function can_access_service_by_alias(): void
    {
        $rendered = self::container()->get('twig')->render('template1.html.twig');

        $this->assertSame("prop value\nmethod return 1\nmethod return 2\n", $rendered);
    }

    /**
     * @test
     */
    public function invalid_service_alias(): void
    {
        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('Twig service with alias "invalid" is not registered. Registered services: "service-a, service-b"');

        self::container()->get('twig')->render('template2.html.twig');
    }

    private static function container(): ContainerInterface
    {
        if (\method_exists(self::class, 'getContainer')) {
            return self::getContainer();
        }

        self::bootKernel();

        return self::$container;
    }
}
