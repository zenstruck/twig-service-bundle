<?php

/*
 * This file is part of the zenstruck/twig-service-bundle package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

        $this->assertSame("prop value\nmethod return 1\nmethod return 2\nprop value\nmethod return 1\nmethod return 2\n", $rendered);
    }

    /**
     * @test
     */
    public function invalid_service_alias(): void
    {
        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('Twig service with alias "invalid" is not registered. Registered services: "service_a, service_b"');

        self::getContainer()->get('twig')->render('template2.html.twig');
    }

    /**
     * @test
     */
    public function invokable_service_filter(): void
    {
        $rendered = self::getContainer()->get('twig')->render('template3.html.twig');

        $this->assertSame("foo\nfoo bar baz\nfoo\nfoo bar baz\n", $rendered);
    }

    /**
     * @test
     */
    public function invokable_service_filter_must_be_invokable(): void
    {
        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('Twig service "service_b" (Zenstruck\Twig\Tests\Fixture\ServiceB) must be implement "__invoke()" to be used as an invokable service filter.');

        self::getContainer()->get('twig')->render('template4.html.twig');
    }

    /**
     * @test
     */
    public function invalid_invokable_service_alias(): void
    {
        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('Twig service with alias "invalid" is not registered. Registered services: "service_a, service_b"');

        self::getContainer()->get('twig')->render('template5.html.twig');
    }

    /**
     * @test
     */
    public function can_use_parameter_function(): void
    {
        $rendered = self::getContainer()->get('twig')->render('template6.html.twig');

        $this->assertSame("bar\n", $rendered);
    }

    /**
     * @test
     */
    public function fn_functions_and_filters(): void
    {
        $rendered = self::getContainer()->get('twig')->render('template7.html.twig');

        $this->assertSame("first\nfirstfoobar\nsecond\nsecondfoobar\nthird\nthirdfoobar\nfirstfoo\nfirstfoobar\nsecondfoo\nsecondfoobar\nthirdfoo\nthirdfoobar\n", $rendered);
    }

    /**
     * @test
     */
    public function dynamic_fn_functions_and_filters(): void
    {
        $rendered = self::getContainer()->get('twig')->render('template8.html.twig');

        $this->assertSame("first\nfirstfoobar\nsecond\nsecondfoobar\nthird\nthirdfoobar\nfirstfoo\nfirstfoobar\nsecondfoo\nsecondfoobar\nthirdfoo\nthirdfoobar\n", $rendered);
    }

    /**
     * @test
     */
    public function fn_as_service_method_functions_and_filters(): void
    {
        $rendered = self::getContainer()->get('twig')->render('template9.html.twig');

        $this->assertSame("method1\nmethod1foobar\nmethod2\nmethod2foobar\nmethod3\nmethod3foobar\nmethod1foo\nmethod1foobar\nmethod2foo\nmethod2foobar\nmethod3foo\nmethod3foobar\n", $rendered);
    }

    /**
     * @test
     */
    public function dynamic_fn_as_service_method_functions_and_filters(): void
    {
        $rendered = self::getContainer()->get('twig')->render('template10.html.twig');

        $this->assertSame("method1\nmethod1foobar\nmethod2\nmethod2foobar\nmethod3\nmethod3foobar\nmethod1foo\nmethod1foobar\nmethod2foo\nmethod2foobar\nmethod3foo\nmethod3foobar\n", $rendered);
    }

    /**
     * @test
     */
    public function configured_fn_as_function_and_filter(): void
    {
        $rendered = self::getContainer()->get('twig')->render('template11.html.twig');

        $this->assertSame(<<<EOF
            3
            foo
            someMethod1foobar
            someMethod2foobar
            3
            foo
            someMethod1foobar
            someMethod2foobar\n
            EOF,
            $rendered
        );
    }

    /**
     * @test
     */
    public function configured_fn_as_dynamic_function_and_filter(): void
    {
        $rendered = self::getContainer()->get('twig')->render('template12.html.twig');

        $this->assertSame(<<<EOF
            3
            foo
            someMethod1foobar
            someMethod2foobar
            3
            foo
            someMethod1foobar
            someMethod2foobar\n
            EOF,
            $rendered
        );
    }
}
