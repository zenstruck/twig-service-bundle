<?php

/*
 * This file is part of the zenstruck/twig-service-bundle package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Twig\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Console\Test\InteractsWithConsole;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ListCommandTest extends KernelTestCase
{
    use InteractsWithConsole;

    /**
     * @test
     */
    public function execute(): void
    {
        $this->executeConsoleCommand('zenstruck:twig-service:list')
            ->assertOutputContains('Available Functions/Filters')
            ->assertOutputContains('strlen             strlen')
            ->assertOutputContains('someMethod1        Zenstruck\Twig\Tests\Fixture\SomeClass::someMethod1()')
            ->assertOutputContains('router             Symfony\Bundle\FrameworkBundle\Routing\Router->generate()')
            ->assertOutputContains('method1            Zenstruck\Twig\Tests\Fixture\ServiceC->method1()')
            ->assertOutputContains('second             App\second')
            ->assertOutputContains('Available Services')
            ->assertOutputContains('service_a   Zenstruck\Twig\Tests\Fixture\ServiceA   yes')
            ->assertOutputContains('service_b   Zenstruck\Twig\Tests\Fixture\ServiceB   no')
        ;
    }
}
