<?php

/*
 * This file is part of the zenstruck/twig-service-bundle package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Twig;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @readonly
 */
#[\Attribute(\Attribute::TARGET_FUNCTION | \Attribute::TARGET_METHOD)]
final class AsTwigFunction
{
    public const THROW = '__throw';

    public function __construct(
        public ?string $alias = null,
        public mixed $onExceptionReturn = self::THROW,
    ) {
    }
}
