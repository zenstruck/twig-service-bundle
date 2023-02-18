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
final class TwigFunctionRuntime
{
    /**
     * @param array<string,callable-string> $functions
     */
    public function __construct(private array $functions)
    {
    }

    public function call(string $alias, mixed ...$args): mixed
    {
        if (isset($this->functions[$alias])) {
            return $this->functions[$alias](...$args);
        }

        throw new \RuntimeException(\sprintf('Twig function with alias "%s" is not registered. Registered functions: "%s"', $alias, \implode(', ', \array_keys($this->functions))));
    }

    public function filter(mixed $value, string $alias, mixed ...$args): mixed
    {
        return $this->call($alias, $value, ...$args);
    }
}
