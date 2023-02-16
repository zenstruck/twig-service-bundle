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

use Zenstruck\Twig\AsTwigService;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[AsTwigService('service_a')]
final class ServiceA
{
    public string $property = 'prop value';

    public function __invoke(string $value, string ...$extra): string
    {
        return \implode(' ', [$value, ...$extra]);
    }

    public function method(int $value): string
    {
        return 'method return '.$value;
    }
}
