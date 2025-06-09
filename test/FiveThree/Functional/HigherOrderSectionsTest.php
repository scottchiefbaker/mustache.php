<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mustache\Test\FiveThree\Functional;

use Mustache\Engine;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * @group lambdas
 * @group functional
 */
class HigherOrderSectionsTest extends TestCase
{
    private $mustache;

    public function set_up()
    {
        $this->mustache = new Engine();
    }

    public function testAnonymousFunctionSectionCallback()
    {
        $tpl = $this->mustache->loadTemplate('{{#wrapper}}{{name}}{{/wrapper}}');

        $foo = new Foo();
        $foo->name = 'Mario';
        $foo->wrapper = function ($text) {
            return sprintf('<div class="anonymous">%s</div>', $text);
        };

        $this->assertEquals(sprintf('<div class="anonymous">%s</div>', $foo->name), $tpl->render($foo));
    }

    public function testSectionCallback()
    {
        $one = $this->mustache->loadTemplate('{{name}}');
        $two = $this->mustache->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');

        $foo = new Foo();
        $foo->name = 'Luigi';

        $this->assertEquals($foo->name, $one->render($foo));
        $this->assertEquals(sprintf('<em>%s</em>', $foo->name), $two->render($foo));
    }

    public function testViewArrayAnonymousSectionCallback()
    {
        $tpl = $this->mustache->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');

        $data = [
            'name' => 'Bob',
            'wrap' => function ($text) {
                return sprintf('[[%s]]', $text);
            },
        ];

        $this->assertEquals(sprintf('[[%s]]', $data['name']), $tpl->render($data));
    }

    /**
     * @dataProvider nonTemplateLambdasData
     */
    public function testNonTemplateLambdas($tpl, $data, $expect)
    {
        $this->assertEquals($expect, $this->mustache->render($tpl, $data));
    }

    public function nonTemplateLambdasData()
    {
        $data = [
            'lang' => 'en-US',
            'people' => function () {
                return [
                    (object) ['name' => 'Albert', 'lang' => 'en-GB'],
                    (object) ['name' => 'Betty'],
                    (object) ['name' => 'Charles'],
                ];
            },
        ];

        return [
            ["{{# people }} - {{ name }}\n{{/people}}", $data, " - Albert\n - Betty\n - Charles\n"],
            ["{{# people }} - {{ name }}: {{ lang }}\n{{/people}}", $data, " - Albert: en-GB\n - Betty: en-US\n - Charles: en-US\n"],
        ];
    }
}

class Foo
{
    public $name  = 'Justin';
    public $lorem = 'Lorem ipsum dolor sit amet,';
    public $wrap;

    public function __construct()
    {
        $this->wrap = function ($text) {
            return sprintf('<em>%s</em>', $text);
        };
    }
}
