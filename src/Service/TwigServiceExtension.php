<?php

namespace Zenstruck\Twig\Service;

use Twig\Extension\AbstractExtension;
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
        return [new TwigFunction('service', [TwigServiceRuntime::class, 'get'])];
    }
}
