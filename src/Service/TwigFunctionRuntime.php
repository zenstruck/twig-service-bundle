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

use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class TwigFunctionRuntime
{
    /**
     * @param array<string,callable-string|array{0:string,1:string}> $functions
     */
    public function __construct(private ServiceLocator $container, private array $functions)
    {
    }

    public function call(string $alias, mixed ...$args): mixed
    {
        if (!$callable = $this->functions[$alias] ?? null) {
            throw new \RuntimeException(\sprintf('Twig function with alias "%s" is not registered. Registered functions: "%s"', $alias, \implode(', ', \array_keys($this->functions))));
        }

        if (\is_string($callable)) {
            return $callable(...$args);
        }

        return $this->container->get($callable[0])->{$callable[1]}(...$args);
    }

    public function filter(mixed $value, string $alias, mixed ...$args): mixed
    {
        return $this->call($alias, $value, ...$args);
    }
}
