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
    private ServiceLocator $container;

    public function __construct(ServiceLocator $container)
    {
        $this->container = $container;
    }

    public function get(string $alias): object
    {
        try {
            return $this->container->get($alias);
        } catch (NotFoundExceptionInterface $e) {
            throw new \RuntimeException(\sprintf('Twig service with alias "%s" is not registered. Registered services: "%s"', $alias, \implode(', ', \array_keys($this->container->getProvidedServices()))));
        }
    }
}
