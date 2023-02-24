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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SomeClass
{
    public static function someMethod1(string ...$args): string
    {
        return 'someMethod1'.\implode('', $args);
    }

    public static function someMethod2(string ...$args): string
    {
        return 'someMethod2'.\implode('', $args);
    }
}
