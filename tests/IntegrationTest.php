<?php

namespace Zenstruck\Twig\Tests;

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
        $rendered = self::getContainer()->get('twig')->render('template1.html.twig');

        $this->assertSame("prop value\nmethod return 1\nmethod return 2\n", $rendered);
    }

    /**
     * @test
     */
    public function invalid_service_alias(): void
    {
        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('Twig service with alias "invalid" is not registered. Registered services: "service-a, service-b"');

        self::getContainer()->get('twig')->render('template2.html.twig');
    }

    /**
     * @test
     */
    public function invokable_service_filter(): void
    {
        $rendered = self::getContainer()->get('twig')->render('template3.html.twig');

        $this->assertSame("foo\nfoo bar baz\n", $rendered);
    }

    /**
     * @test
     */
    public function invokable_service_filter_must_be_invokable(): void
    {
        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('Twig service "service-b" (Zenstruck\Twig\Tests\Fixture\ServiceB) must be implement "__invoke()" to be used as an invokable service filter.');

        self::getContainer()->get('twig')->render('template4.html.twig');
    }

    /**
     * @test
     */
    public function invalid_invokable_service_alias(): void
    {
        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('Twig service with alias "invalid" is not registered. Registered services: "service-a, service-b"');

        self::getContainer()->get('twig')->render('template5.html.twig');
    }
}
