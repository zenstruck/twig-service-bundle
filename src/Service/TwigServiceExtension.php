<?php

/*
 * This file is part of the zenstruck/twig-service-bundle package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Twig\Service;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class TwigServiceExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('service', [TwigServiceRuntime::class, 'get']),
            new TwigFunction('service_*', [TwigServiceRuntime::class, 'get']),
            new TwigFunction('parameter', [TwigServiceRuntime::class, 'parameter']),
            new TwigFunction('fn', [TwigFunctionRuntime::class, 'call']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('service', [TwigServiceRuntime::class, 'filter']),
            new TwigFilter('service_*', [TwigServiceRuntime::class, 'dynamicFilter']),
            new TwigFilter('fn', [TwigFunctionRuntime::class, 'filter']),
        ];
    }
}
