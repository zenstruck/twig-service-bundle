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
final class InvokableServiceMethod extends Invokable
{
    /** @var array{0:object,string}&callable */
    private $callable;

    public function __construct(mixed $onExceptionReturn, object $service, string $method)
    {
        if (!\is_callable($callable = [$service, $method])) {
            throw new \InvalidArgumentException('not callable...');
        }

        $this->callable = $callable;

        parent::__construct($onExceptionReturn);
    }

    public function __toString(): string
    {
        $class = $this->callable[0]::class;

        return "{$class}->{$this->callable[1]}()";
    }

    public function callable(): callable
    {
        return $this->callable;
    }
}
