<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2025 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mustache\Test;

use Mustache\Context;
use Mustache\Engine;
use Mustache\Template;

/**
 * @group unit
 */
class TemplateTest extends TestCase
{
    public function testConstructor()
    {
        $mustache = new Engine();
        $template = new TemplateStub($mustache);
        $this->assertSame($mustache, $template->getMustache());
    }

    public function testRendering()
    {
        $rendered = '<< wheee >>';
        $mustache = new Engine();
        $template = new TemplateStub($mustache);
        $template->rendered = $rendered;
        $context  = new Context();

        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $this->assertSame($rendered, $template());
        }

        $this->assertSame($rendered, $template->render());
        $this->assertSame($rendered, $template->renderInternal($context));
        $this->assertSame($rendered, $template->render(['foo' => 'bar']));
    }
}

class TemplateStub extends Template
{
    public $rendered;

    public function getMustache()
    {
        return $this->mustache;
    }

    public function renderInternal(Context $context, $indent = '', $escape = false)
    {
        return $this->rendered;
    }
}
