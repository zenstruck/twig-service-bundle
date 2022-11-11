<?php

namespace Zenstruck\Twig\Service;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class TwigServiceRuntime
{
    public function __construct(private ServiceLocator $container)
    {
    }

    public function get(string $alias): object
    {
        try {
            return $this->container->get($alias);
        } catch (NotFoundExceptionInterface $e) {
            throw new \RuntimeException(\sprintf('Twig service with alias "%s" is not registered. Registered services: "%s"', $alias, \implode(', ', \array_keys($this->container->getProvidedServices()))));
        }
    }

    public function filter(mixed $value, string $alias, mixed ...$args): mixed
    {
        $service = $this->get($alias);

        if (!\is_callable($service)) {
            throw new \RuntimeException(\sprintf('Twig service "%s" (%s) must be implement "__invoke()" to be used as an invokable service filter.', $alias, \get_class($service)));
        }

        return $service($value, ...$args);
    }
}
