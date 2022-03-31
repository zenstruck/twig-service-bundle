<?php

namespace Zenstruck\Twig\Tests\Fixture;

use Zenstruck\Twig\AsTwigService;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[AsTwigService('service-a')]
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
