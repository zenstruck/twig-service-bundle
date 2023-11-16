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

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class TwigFunctionRuntime
{
    /**
     * @param ServiceProviderInterface<callable> $functions
     */
    public function __construct(private ServiceProviderInterface $functions)
    {
    }

    public function call(string $alias, mixed ...$args): mixed
    {
        try {
            return $this->functions->get($alias)(...$args);
        } catch (NotFoundExceptionInterface $e) {
            throw new \RuntimeException(\sprintf('Twig function with alias "%s" is not registered. Registered functions: "%s"', $alias, \implode(', ', \array_keys($this->functions->getProvidedServices()))), previous: $e);
        }
    }

    public function filter(mixed $value, string $alias, mixed ...$args): mixed
    {
        return $this->call($alias, $value, ...$args);
    }

    /**
     * @return ServiceProviderInterface<callable>
     */
    public function functions(): ServiceProviderInterface
    {
        return $this->functions;
    }
}
