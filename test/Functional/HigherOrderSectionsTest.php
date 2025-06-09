<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2025 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mustache\Test\Functional;

use Mustache\Engine;
use Mustache\Test\FunctionalTestCase;

/**
 * @group lambdas
 * @group functional
 */
class HigherOrderSectionsTest extends FunctionalTestCase
{
    private $mustache;

    public function set_up()
    {
        $this->mustache = new Engine();
    }

    /**
     * @dataProvider sectionCallbackData
     */
    public function testSectionCallback($data, $tpl, $expect)
    {
        $this->assertEquals($expect, $this->mustache->render($tpl, $data));
    }

    public function sectionCallbackData()
    {
        $foo = new Foo();
        $foo->doublewrap = [$foo, 'wrapWithBoth'];

        $bar = new Foo();
        $bar->trimmer = [get_class($bar), 'staticTrim'];

        return [
            [$foo, '{{#doublewrap}}{{name}}{{/doublewrap}}', sprintf('<strong><em>%s</em></strong>', $foo->name)],
            [$bar, '{{#trimmer}}   {{name}}   {{/trimmer}}', $bar->name],
        ];
    }

    public function testViewArraySectionCallback()
    {
        $tpl = $this->mustache->loadTemplate('{{#trim}}    {{name}}    {{/trim}}');

        $foo = new Foo();

        $data = [
            'name' => 'Bob',
            'trim' => [get_class($foo), 'staticTrim'],
        ];

        $this->assertEquals($data['name'], $tpl->render($data));
    }

    public function testMonsters()
    {
        $tpl = $this->mustache->loadTemplate('{{#title}}{{title}} {{/title}}{{name}}');

        $frank = new Monster();
        $frank->title = 'Dr.';
        $frank->name  = 'Frankenstein';
        $this->assertEquals('Dr. Frankenstein', $tpl->render($frank));

        $dracula = new Monster();
        $dracula->title = 'Count';
        $dracula->name  = 'Dracula';
        $this->assertEquals('Count Dracula', $tpl->render($dracula));
    }

    public function testPassthroughOptimization()
    {
        $mustache = $this->getMockBuilder(Engine::class);
        if (method_exists($mustache, 'onlyMethods')) {
            $mustache->onlyMethods(['loadLambda']);
        } else {
            $mustache->setMethods(['loadLambda']);
        }
        $mustache = $mustache->getMock();

        $mustache->expects($this->never())
            ->method('loadLambda');

        $tpl = $mustache->loadTemplate('{{#wrap}}NAME{{/wrap}}');

        $foo = new Foo();
        $foo->wrap = [$foo, 'wrapWithEm'];

        $this->assertEquals('<em>NAME</em>', $tpl->render($foo));
    }

    public function testWithoutPassthroughOptimization()
    {
        $mustache = $this->getMockBuilder(Engine::class);
        if (method_exists($mustache, 'onlyMethods')) {
            $mustache->onlyMethods(['loadLambda']);
        } else {
            $mustache->setMethods(['loadLambda']);
        }
        $mustache = $mustache->getMock();

        $mustache->expects($this->once())
            ->method('loadLambda')
            ->will($this->returnValue($mustache->loadTemplate('<em>{{ name }}</em>')));

        $tpl = $mustache->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');

        $foo = new Foo();
        $foo->wrap = [$foo, 'wrapWithEm'];

        $this->assertEquals('<em>' . $foo->name . '</em>', $tpl->render($foo));
    }

    /**
     * @dataProvider cacheLambdaTemplatesData
     */
    public function testCacheLambdaTemplatesOptionWorks($dirName, $tplPrefix, $enable, $expect)
    {
        $cacheDir = $this->setUpCacheDir($dirName);
        $mustache = new Engine([
            'template_class_prefix'  => $tplPrefix,
            'cache'                  => $cacheDir,
            'cache_lambda_templates' => $enable,
        ]);

        $tpl = $mustache->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');
        $foo = new Foo();
        $foo->wrap = [$foo, 'wrapWithEm'];
        $this->assertEquals('<em>' . $foo->name . '</em>', $tpl->render($foo));
        $this->assertCount($expect, glob($cacheDir . '/*.php'));
    }

    public function cacheLambdaTemplatesData()
    {
        return [
            ['test_enabling_lambda_cache',  '_TestEnablingLambdaCache_',  true,  2],
            ['test_disabling_lambda_cache', '_TestDisablingLambdaCache_', false, 1],
        ];
    }

    protected function setUpCacheDir($name)
    {
        $cacheDir = self::$tempDir . '/' . $name;
        if (file_exists($cacheDir)) {
            self::rmdir($cacheDir);
        }
        mkdir($cacheDir, 0777, true);

        return $cacheDir;
    }

    public function testAnonymousFunctionSectionCallback()
    {
        $tpl = $this->mustache->loadTemplate('{{#wrapper}}{{name}}{{/wrapper}}');

        $foo = new Bar();
        $foo->name = 'Mario';
        $foo->wrapper = function ($text) {
            return sprintf('<div class="anonymous">%s</div>', $text);
        };

        $this->assertEquals(sprintf('<div class="anonymous">%s</div>', $foo->name), $tpl->render($foo));
    }

    public function testAnonymousSectionCallback()
    {
        $one = $this->mustache->loadTemplate('{{name}}');
        $two = $this->mustache->loadTemplate('{{#wrap}}{{name}}{{/wrap}}');

        $foo = new Bar();
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
    public $name = 'Justin';
    public $lorem = 'Lorem ipsum dolor sit amet,';

    public $doublewrap;
    public $trimmer;

    public function wrapWithEm($text)
    {
        return sprintf('<em>%s</em>', $text);
    }

    /**
     * @param string $text
     */
    public function wrapWithStrong($text)
    {
        return sprintf('<strong>%s</strong>', $text);
    }

    public function wrapWithBoth($text)
    {
        return self::wrapWithStrong(self::wrapWithEm($text));
    }

    public static function staticTrim($text)
    {
        return trim($text);
    }
}

class Bar
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

class Monster
{
    public $title;
    public $name;
}
