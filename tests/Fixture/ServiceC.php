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

use Zenstruck\Twig\AsTwigFunction;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ServiceC
{
    #[AsTwigFunction]
    public function method1(string ...$args): string
    {
        return 'method1'.\implode('', $args);
    }

    #[AsTwigFunction('custom_method')]
    public function method2(string ...$args): string
    {
        return 'method2'.\implode('', $args);
    }

    #[AsTwigFunction]
    public static function method3(string ...$args): string
    {
        return 'method3'.\implode('', $args);
    }
}
