# zenstruck/twig-service-bundle

[![CI](https://github.com/zenstruck/twig-service-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/zenstruck/twig-service-bundle/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/zenstruck/twig-service-bundle/branch/1.x/graph/badge.svg?token=ZK1XSG6X35)](https://codecov.io/gh/zenstruck/twig-service-bundle)

Making data from your app's services available in twig templates can be done by either:
1. Injecting the service/data into the template when rendering.
2. Creating a twig extension that has access to the service/data.

For #1, this isn't always a viable option (ie you need this data in your layout).
With #2, there is a bit of boilerplate and if done incorrectly (ie not using a
[runtime](https://symfony.com/doc/current/templating/twig_extension.html#creating-lazy-loaded-twig-extensions)
or [service proxy](https://symfony.com/doc/current/service_container/lazy_services.html)
for heavy services), it could lead to performance issues.

This bundle provides a way to mark any service as a _twig service_. You can then
access this service _lazily_ in any template via an _alias_.

## Installation

```bash
composer require zenstruck/signed-url-bundle
```

**NOTE**: If not added automatically by `symfony/flex`, enable `ZenstruckTwigServiceBundle`.

## Usage

Mark any service you'd like to make available in twig templates with the `AsTwigService`
attribute which requires an _alias_:

```php
namespace App\Repository;

// ...
use Zenstruck\Twig\AsTwigService;

#[AsTwigService(alias: 'post-repo')]
class PostRepository extends ServiceEntityRepository
{
    /**
     * @return Post[]
     */
    public function latestPosts(int $number = 10): array
    {
        // ...
    }
}
```

**NOTE**: If not using autowiring, on PHP 7, or Symfony < 5.3, you'll need to register
the service and add the `twig.service` tag:

```yaml
services:
    App\Repository\PostRepository:
        tags:
            - { name: twig.service, alias: post-repo }
```

You're now ready to access the service in any twig template:

```twig
{% for post in service('post-repo').latestPosts(5) %}
    {# ... #}
{% endfor %}
```
