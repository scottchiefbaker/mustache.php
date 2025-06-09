<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2025 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mustache\Test\Loader;

use Mustache\Exception\UnknownTemplateException;
use Mustache\Loader\ArrayLoader;
use Mustache\Loader\CascadingLoader;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * @group unit
 */
class CascadingLoaderTest extends TestCase
{
    public function testLoadTemplates()
    {
        $loader = new CascadingLoader([
            new ArrayLoader(['foo' => '{{ foo }}']),
            new ArrayLoader(['bar' => '{{#bar}}BAR{{/bar}}']),
        ]);

        $this->assertEquals('{{ foo }}', $loader->load('foo'));
        $this->assertEquals('{{#bar}}BAR{{/bar}}', $loader->load('bar'));
    }

    public function testMissingTemplatesThrowExceptions()
    {
        $this->expectException(UnknownTemplateException::class);
        $loader = new CascadingLoader([
            new ArrayLoader(['foo' => '{{ foo }}']),
            new ArrayLoader(['bar' => '{{#bar}}BAR{{/bar}}']),
        ]);

        $loader->load('not_a_real_template');
    }
}
