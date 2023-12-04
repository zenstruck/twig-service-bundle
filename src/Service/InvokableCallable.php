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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class InvokableCallable extends Invokable
{
    /** @var callable-string|(array{0:class-string,string}&callable) */
    private $callable;

    /**
     * @param callable-string|(array{0:class-string,string}&callable) $callable
     */
    public function __construct(mixed $onExceptionReturn, callable $callable)
    {
        $this->callable = $callable;

        parent::__construct($onExceptionReturn);
    }

    public function __toString(): string
    {
        return \is_string($this->callable) ? $this->callable : "{$this->callable[0]}::{$this->callable[1]}()";
    }

    public function callable(): callable
    {
        return $this->callable;
    }
}
