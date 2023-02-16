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

While you can mark any service as a _twig service_, it is not recommended to mark services
that have nothing to do with templating (ie repositories) as such. You can think of twig
services as _lightweight-lazy-twig-extension-functions_ whose purpose is to break up/simplify
large custom twig extensions.

## Installation

```bash
composer require zenstruck/twig-service-bundle
```

> **Note**: If not added automatically by `symfony/flex`, enable `ZenstruckTwigServiceBundle`.

## Usage

### Service Function

Mark any service you'd like to make available in twig templates with the `AsTwigService`
attribute which requires an _alias_:

```php
namespace App\Twig\Service;

// ...
use Zenstruck\Twig\AsTwigService;

#[AsTwigService(alias: 'posts')]
class PostService
{
    private PostRepository $repo;

    public function __construct(PostRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @return Post[]
     */
    public function latestPosts(int $number = 10): array
    {
        return $this->repo->findLatestPosts($number);
    }
}
```

You're now ready to access the service in any twig template:

```twig
{% for post in service('posts').latestPosts(5) %}
    {# ... #}
{% endfor %}
```

There is also a dynamic function. The following is equivalent to above:

```twig
{% for post in service_posts().latestPosts(5) %}
    {# ... #}
{% endfor %}
```

### Invokable Filters

You can turn any twig service into a twig filter by having it implement `__invoke()`:

```php
namespace App\Twig\Service;

// ...
use Zenstruck\Twig\AsTwigService;

#[AsTwigService(alias: 'image_transformer')]
class ImageTransformer
{
    public function __invoke(string $imageUrl, string ...$transformations): string
    {
        // adds transformation to url and returns new url
    }
}
```

In your template, use the `service` twig filter:

```twig
{{ url|service('image_transformer', 'square-200', 'watermark') }}
```

There is also a dynamic filter. The following is equivalent to above:

```twig
{{ url|service_image_transformer('square-200', 'watermark') }}
```

Note, the output will be escaped. If your filter returns html that you don't want
escaped, use the `raw` filter:

```twig
{{ url|service_image_transformer('square-200', 'watermark')|raw }}
```
