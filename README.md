# Mustache.php

A [Mustache][mustache] implementation in PHP.

[![Package version](http://img.shields.io/packagist/v/mustache/mustache.svg?style=flat-square)][packagist]
[![Monthly downloads](http://img.shields.io/packagist/dm/mustache/mustache.svg?style=flat-square)][packagist]


## Installation

```
composer require mustache/mustache
```

## Usage

A quick example:

```php
<?php
$m = new \Mustache\Engine(['entity_flags' => ENT_QUOTES]);
echo $m->render('Hello {{planet}}', ['planet' => 'World!']); // "Hello World!"
```


And a more in-depth example -- this is the canonical Mustache template:

```html+jinja
Hello {{name}}
You have just won {{value}} dollars!
{{#in_ca}}
Well, {{taxed_value}} dollars, after taxes.
{{/in_ca}}
```


Create a view "context" object -- which could also be an associative array, but those don't do functions quite as well:

```php
<?php
class Chris {
    public $name  = "Chris";
    public $value = 10000;

    public function taxed_value() {
        return $this->value - ($this->value * 0.4);
    }

    public $in_ca = true;
}
```


And render it:

```php
<?php
$m = new \Mustache\Engine(['entity_flags' => ENT_QUOTES]);
$chris = new \Chris;
echo $m->render($template, $chris);
```

*Note:* we recommend using `ENT_QUOTES` as a default of [entity_flags][entity_flags] to decrease the chance of Cross-site scripting vulnerability.


## And That's Not All!

Read [the Mustache.php documentation][docs] for more information.


## Upgrading from v2.x

_Mustache.php v3.x drops support for PHP 5.2–5.5_, but is otherwise a drop-in replacement for v2.x:

 - `\Mustache_Engine` and other prefixed classnames are all available as `\Mustache\Engine`, etc. You can keep using the old style for now, if you feel nostalgic for 2008, but you'll want to update eventually.
 - [A context shadowing bug from v2.x has been fixed](https://github.com/bobthecow/mustache.php/commit/66ecb327ce15b9efa0cfcb7026fdc62c6659b27f), but if you depend on the previous buggy behavior you can preserve it via the `buggy_property_shadowing` config option.
 - The `strict_callables` config now defaults to `true`. Lambda sections should use closures or callable objects. To continue supporting array-style callables for lambda sections (e.g. `[$this, 'foo']`), set `strict_callables` to `false`.


## See Also

 - [mustache(5)][manpage] man page.
 - [Readme for the Ruby Mustache implementation][ruby].


[mustache]:     https://mustache.github.io/
[packagist]:    https://packagist.org/packages/mustache/mustache
[entity_flags]: https://github.com/bobthecow/mustache.php/wiki#entity_flags
[docs]:         https://github.com/bobthecow/mustache.php/wiki/Home
[manpage]:      http://mustache.github.io/mustache.5.html
[ruby]:         http://github.com/defunkt/mustache/blob/master/README.md
