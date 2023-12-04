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

use Zenstruck\Twig\AsTwigFunction;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
abstract class Invokable implements \Stringable
{
    public function __construct(private mixed $onExceptionReturn)
    {
    }

    final public function __invoke(mixed ...$args): mixed
    {
        try {
            return $this->callable()(...$args);
        } catch (\Exception $e) {
            if (AsTwigFunction::THROW === $this->onExceptionReturn) {
                throw $e;
            }

            return $this->onExceptionReturn;
        }
    }

    final public function onExceptionReturn(): mixed
    {
        return $this->onExceptionReturn;
    }

    abstract public function callable(): callable;
}
