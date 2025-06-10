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

use Mustache\Engine;

class LambdaHelperTest extends TestCase
{
    private $mustache;

    public function set_up()
    {
        $this->mustache = new Engine();
    }

    public function testSectionLambdaHelper()
    {
        $one = $this->mustache->loadTemplate('{{name}}');
        $two = $this->mustache->loadTemplate('{{#lambda}}{{name}}{{/lambda}}');

        $foo = new \StdClass();
        $foo->name = 'Mario';
        $foo->lambda = function ($text, $mustache) {
            return strtoupper($mustache->render($text));
        };

        $this->assertSame('Mario', $one->render($foo));
        $this->assertSame('MARIO', $two->render($foo));
    }

    public function testSectionLambdaHelperRespectsDelimiterChanges()
    {
        $tpl = $this->mustache->loadTemplate("{{=<% %>=}}\n<%# bang %><% value %><%/ bang %>");

        $data = new \StdClass();
        $data->value = 'hello world';
        $data->bang = function ($text, $mustache) {
            return $mustache->render($text) . '!';
        };

        $this->assertSame('hello world!', $tpl->render($data));
    }

    public function testLambdaHelperIsInvokable()
    {
        $one = $this->mustache->loadTemplate('{{name}}');
        $two = $this->mustache->loadTemplate('{{#lambda}}{{name}}{{/lambda}}');

        $foo = new \StdClass();
        $foo->name = 'Mario';
        $foo->lambda = function ($text, $render) {
            return strtoupper($render($text));
        };

        $this->assertSame('Mario', $one->render($foo));
        $this->assertSame('MARIO', $two->render($foo));
    }
}
