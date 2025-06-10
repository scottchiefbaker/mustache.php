<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2025 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mustache\Test\Behavior;

use Mustache\Engine;
use Mustache\Test\TestCase;

class ObjectSectionTest extends TestCase
{
    private $mustache;

    public function set_up()
    {
        $this->mustache = new Engine();
    }

    public function testBasicObject()
    {
        $tpl = $this->mustache->loadTemplate('{{#foo}}{{name}}{{/foo}}');
        $this->assertSame('Foo', $tpl->render(new Alpha()));
    }

    public function testObjectWithGet()
    {
        $tpl = $this->mustache->loadTemplate('{{#foo}}{{name}}{{/foo}}');
        $this->assertSame('Foo', $tpl->render(new Beta()));
    }

    public function testSectionObjectWithGet()
    {
        $tpl = $this->mustache->loadTemplate('{{#bar}}{{#foo}}{{name}}{{/foo}}{{/bar}}');
        $this->assertSame('Foo', $tpl->render(new Gamma()));
    }

    public function testSectionObjectWithFunction()
    {
        $tpl = $this->mustache->loadTemplate('{{#foo}}{{name}}{{/foo}}');
        $alpha = new Alpha();
        $alpha->foo = new Delta();
        $this->assertSame('Foo', $tpl->render($alpha));
    }
}

class Alpha
{
    public $foo;

    public function __construct()
    {
        $this->foo = new \StdClass();
        $this->foo->name = 'Foo';
        $this->foo->number = 1;
    }
}

class Beta
{
    protected $_data = [];

    public function __construct()
    {
        $this->_data['foo'] = new \StdClass();
        $this->_data['foo']->name = 'Foo';
        $this->_data['foo']->number = 1;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->_data);
    }

    public function __get($name)
    {
        return $this->_data[$name];
    }
}

class Gamma
{
    public $bar;

    public function __construct()
    {
        $this->bar = new Beta();
    }
}

class Delta
{
    protected $_name = 'Foo';

    public function name()
    {
        return $this->_name;
    }
}
