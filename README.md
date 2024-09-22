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

This bundle provides an easy way to make functions, static methods, service methods, and
even full service objects available in your twig templates.

## Installation

```bash
composer require zenstruck/twig-service-bundle
```

> [!NOTE]
> If not added automatically by `symfony/flex`, enable `ZenstruckTwigServiceBundle`.

## Usage

> [!NOTE]
> The output for the following functions/filters will be escaped. If your
> function\filter returns html that you don't want escaped, use the `|raw` filter.

### Service Methods as Functions/Filters

You can mark any public method in your configured services with the `#[AsTwigFunction]`
attribute to make them available within your twig templates with the `fn()` twig
function/filter:

```php
// ...
use Zenstruck\Twig\AsTwigFunction;

class SomeService
{
    // ...

    #[AsTwigFunction] // will be available as "fn_someMethod()" in twig
    public function someMethod($arg1, $arg2): string
    {
        // ...
    }

    #[AsTwigFunction('alias')] // will be available as "fn_alias()" in twig
    public function anotherMethod($arg1, $arg2): string
    {
        // ...
    }
}
```

In your twig template, use the `fn()` function/filter to call:

```twig
{# as a function: #}
{{ fn('someMethod', 'foo', 'bar') }}
{{ fn('alias', 'foo', 'bar') }}

{# as a filter: #}
{{ 'foo'|fn('someMethod', 'bar') }}
{{ 'foo'|fn('alias', 'bar') }}
```

_Dynamic_ functions/filters are made available. The following is equivalent to above:

```twig
{# as a function: #}
{{ fn_someMethod('foo', 'bar') }}
{{ fn_alias('foo', 'bar') }}

{# as a filter: #}
{{ 'foo'|fn_someMethod('bar') }}
{{ 'foo'|fn_alias('bar') }}
```

### User Defined as Functions/Filters

You can mark any of your custom functions with the `#[AsTwigFunction]` attribute
to make them available within your twig templates with the `fn()` twig function\filter:

```php
use Zenstruck\Twig\AsTwigFunction;

#[AsTwigFunction] // will be available as "some_function" in twig
function some_function($arg1, $arg2): string
{
    // ...
}

#[AsTwigFunction('alias')] // will be available as "alias" in twig
function another_function($arg1, $arg2): string
{
    // ...
}
```

In your twig template, use the `fn()` function/filter to call:

```twig
{# as a function: #}
{{ fn('some_function', 'foo', 'bar') }}
{{ fn('alias', 'foo', 'bar') }}

{# as a filter: #}
{{ 'foo'|fn('some_function', 'bar') }}
{{ 'foo'|fn('alias', 'bar') }}
```

_Dynamic_ functions/filters are made available. The following is equivalent to above:

```twig
{# as a function: #}
{{ fn_some_function('foo', 'bar') }}
{{ fn_alias('foo', 'bar') }}

{# as a filter: #}
{{ 'foo'|fn_some_function('bar') }}
{{ 'foo'|fn_alias('bar') }}
```

#### 3rd-Party Functions/Filters

If you need to make functions, static/service methods available in your twig templates
for code you do not control (ie internal PHP functions/3rd party package), you
can configure these in the bundle config:

```yaml
zenstruck_twig_service:
    functions:
        - strlen # available as "fn_strlen()" in twig
        - [service.id, serviceMethod] # available as "fn_serviceMethod()" in twig
        - [Some\Class, somePublicStaticMethod] # available as "fn_somePublicStaticMethod()" in twig

    # use the array key to customize the name
    functions:
        len: strlen # available as "fn_len()" in twig
        custom: [service.id, serviceMethod] # available as "fn_custom()" in twig
        alias: [Some\Class, somePublicStaticMethod] # available as "fn_alias()" in twig
```

### Service Function

Mark any service you'd like to make available in twig templates with the `#[AsTwigService]`.

> [!NOTE]
> While you can mark any service as a _twig service_, it is not recommended to mark services
> that have nothing to do with templating (ie repositories) as such. You can think of twig
> services as _lightweight-lazy-twig-extension-functions_ whose purpose is to break up/simplify
> large custom twig extensions.

```php
namespace App\Twig\Service;

// ...
use Zenstruck\Twig\AsTwigService;

#[AsTwigService(alias: 'posts')]
class PostService
{
    public function __construct(private PostRepository $repo)
    {
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

Each service alias is made available as a _dynamic_ function. The following is equivalent
to above:

```twig
{% for post in service_posts().latestPosts(5) %}
    {# ... #}
{% endfor %}
```

### Invokable Service Filters

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

Each service alias is made available as a _dynamic_ filter. The following is equivalent
to above:

```twig
{{ url|service_image_transformer('square-200', 'watermark') }}
```

### Parameter Function

You can access any service container parameter with the provided `parameter()`
twig function:

```twig
{% for locale in parameter('kernel.enabled_locales') %}
    {# ... #}
{% endfor %}
```

### `zenstruck:twig-service:list` Command

Use this command to list all functions/filters/services configured
by this bundle and available in your twig templates.

> [!NOTE]
> This command is only available when `debug: true`.

```
bin/console zenstruck:twig-service:list

Available Functions/Filters
---------------------------

 // As function: call with fn('{alias}', {...args}) or fn_{alias}({...args})

 // As filter: use as {value}|fn('{alias}', {...args}) or {value}|fn_{alias}({...args})

 ---------- ---------------------------------------------
  Alias      Callable
 ---------- ---------------------------------------------
  strlen     strlen
  generate   @router->generate()
 ---------- ---------------------------------------------

Available Services
------------------

 // Access via service('{alias}') or service_{alias}()

 // If invokable, use as {value}|service('{alias}', {...args}) or {value}|service_{alias}({...args})

 ------- -------------------- ------------
  Alias   Service              Invokable?
 ------- -------------------- ------------
  foo     App\SomeService      yes
  bar     App\AnotherService   no
 ------- -------------------- ------------
```

## Full Default Bundle Configuration

```yaml
zenstruck_twig_service:

    # Callables to make available with fn() twig function/filter
    functions:

        # Examples:
        0:                   strlen # available as "strlen"
        alias:               [Some\Class, somePublicStaticMethod] # available as "alias"
```
