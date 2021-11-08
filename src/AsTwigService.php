<?php

namespace Zenstruck\Twig;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsTwigService
{
    public function __construct(public string $alias)
    {
    }
}
