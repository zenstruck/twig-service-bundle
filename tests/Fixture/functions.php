<?php

/*
 * This file is part of the zenstruck/twig-service-bundle package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace {
    use Zenstruck\Twig\AsTwigFunction;

    #[AsTwigFunction]
    function first(string ...$value): string
    {
        return 'first'.\implode('', $value);
    }
}

namespace App {
    use Zenstruck\Twig\AsTwigFunction;

    #[AsTwigFunction]
    function second(string ...$value): string
    {
        return 'second'.\implode('', $value);
    }

    #[AsTwigFunction('custom_third')]
    function third(string ...$value): string
    {
        return 'third'.\implode('', $value);
    }

    #[AsTwigFunction(onExceptionReturn: 'error!')]
    function error1(): string
    {
        throw new \Exception('ERROR!');
    }

    #[AsTwigFunction(onExceptionReturn: null)]
    function error2(string $value): string
    {
        return $value;
    }
}
