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
use Mustache\Exception\InvalidArgumentException;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * @group unit
 */
class ContextTest extends TestCase
{
    public function testConstructor()
    {
        $one = new Context();
        $this->assertSame('', $one->find('foo'));
        $this->assertSame('', $one->find('bar'));

        $two = new Context([
            'foo' => 'FOO',
            'bar' => '<BAR>',
        ]);
        $this->assertEquals('FOO', $two->find('foo'));
        $this->assertEquals('<BAR>', $two->find('bar'));

        $obj = new \StdClass();
        $obj->name = 'NAME';
        $three = new Context($obj);
        $this->assertSame($obj, $three->last());
        $this->assertEquals('NAME', $three->find('name'));
    }

    public function testPushPopAndLast()
    {
        $context = new Context();
        $this->assertFalse($context->last());

        $dummy = new TestDummy();
        $context->push($dummy);
        $this->assertSame($dummy, $context->last());
        $this->assertSame($dummy, $context->pop());
        $this->assertFalse($context->last());

        $obj = new \StdClass();
        $context->push($dummy);
        $this->assertSame($dummy, $context->last());
        $context->push($obj);
        $this->assertSame($obj, $context->last());
        $this->assertSame($obj, $context->pop());
        $this->assertSame($dummy, $context->pop());
        $this->assertFalse($context->last());
    }

    public function testFind()
    {
        $context = new Context();

        $dummy = new TestDummy();

        $obj = new \StdClass();
        $obj->name = 'obj';

        $arr = [
            'a' => ['b' => ['c' => 'see']],
            'b' => 'bee',
        ];

        $string = 'some arbitrary string';

        $context->push($dummy);
        $this->assertEquals('dummy', $context->find('name'));

        $context->push($obj);
        $this->assertEquals('obj', $context->find('name'));

        $context->pop();
        $this->assertEquals('dummy', $context->find('name'));

        $dummy->name = 'dummyer';
        $this->assertEquals('dummyer', $context->find('name'));

        $context->push($arr);
        $this->assertEquals('bee', $context->find('b'));
        $this->assertEquals('see', $context->findDot('a.b.c'));

        $dummy->name = 'dummy';

        $context->push($string);
        $this->assertSame($string, $context->last());
        $this->assertEquals('dummy', $context->find('name'));
        $this->assertEquals('see', $context->findDot('a.b.c'));
        $this->assertEquals('<foo>', $context->find('foo'));
        $this->assertEquals('<bar>', $context->findDot('bar'));
    }

    public function testArrayAccessFind()
    {
        $access = new TestArrayAccess([
            'a' => ['b' => ['c' => 'see']],
            'b' => 'bee',
        ]);

        $context = new Context($access);
        $this->assertEquals('bee', $context->find('b'));
        $this->assertEquals('see', $context->findDot('a.b.c'));
        $this->assertEquals(null, $context->findDot('a.b.c.d'));
    }

    public function testAccessorPriority()
    {
        $context = new Context(new AllTheThings());

        $this->assertEquals('win', $context->find('foo'), 'method beats property');
        $this->assertEquals('win', $context->find('bar'), 'property beats ArrayAccess');
        $this->assertEquals('win', $context->find('baz'), 'ArrayAccess stands alone');
        $this->assertEquals('win', $context->find('qux'), 'ArrayAccess beats private property');
    }

    public function testAnchoredDotNotation()
    {
        $context = new Context();

        $a = [
            'name'   => 'a',
            'number' => 1,
        ];

        $b = [
            'number' => 2,
            'child'  => [
                'name' => 'baby bee',
            ],
        ];

        $c = [
            'name' => 'cee',
        ];

        $context->push($a);
        $this->assertEquals('a', $context->find('name'));
        $this->assertEquals('', $context->findDot('.name'));
        $this->assertEquals('a', $context->findAnchoredDot('.name'));
        $this->assertEquals(1, $context->find('number'));
        $this->assertEquals('', $context->findDot('.number'));
        $this->assertEquals(1, $context->findAnchoredDot('.number'));

        $context->push($b);
        $this->assertEquals('a', $context->find('name'));
        $this->assertEquals(2, $context->find('number'));
        $this->assertEquals('', $context->findDot('.name'));
        $this->assertEquals('', $context->findDot('.number'));
        $this->assertEquals('', $context->findAnchoredDot('.name'));
        $this->assertEquals(2, $context->findAnchoredDot('.number'));
        $this->assertEquals('baby bee', $context->findDot('child.name'));
        $this->assertEquals('', $context->findDot('.child.name'));
        $this->assertEquals('baby bee', $context->findAnchoredDot('.child.name'));

        $context->push($c);
        $this->assertEquals('cee', $context->find('name'));
        $this->assertEquals('', $context->findDot('.name'));
        $this->assertEquals('cee', $context->findAnchoredDot('.name'));
        $this->assertEquals(2, $context->find('number'));
        $this->assertEquals('', $context->findDot('.number'));
        $this->assertEquals('', $context->findAnchoredDot('.number'));
        $this->assertEquals('baby bee', $context->findDot('child.name'));
        $this->assertEquals('', $context->findDot('.child.name'));
        $this->assertEquals('', $context->findAnchoredDot('.child.name'));
    }

    public function testAnchoredDotNotationThrowsExceptions()
    {
        $this->expectException(InvalidArgumentException::class);
        $context = new Context();
        $context->push(['a' => 1]);
        $context->findAnchoredDot('a');
    }

    public function testNullArrayValueMasking()
    {
        $context = new Context();

        $a = [
            'name' => 'not null',
        ];
        $b = [
            'name' => null,
        ];

        $context->push($a);
        $context->push($b);

        $this->assertNull($context->find('name'));
    }

    public function testNullPropertyValueMasking()
    {
        $context = new Context();

        $a = (object) [
            'name' => 'not null',
        ];
        $b = (object) [
            'name' => null,
        ];

        $context->push($a);
        $context->push($b);

        $this->assertNull($context->find('name'));
    }

    public function testBuggyNullPropertyValueMasking()
    {
        $context = new Context(null, true);

        $a = (object) [
            'name' => 'not null',
        ];
        $b = (object) [
            'name' => null,
        ];

        $context->push($a);
        $context->push($b);

        $this->assertEquals($context->find('name'), 'not null');
    }
}

class TestDummy
{
    public $name = 'dummy';

    public function __invoke()
    {
        // nothing
    }

    public static function foo()
    {
        return '<foo>';
    }

    public function bar()
    {
        return '<bar>';
    }
}

class TestArrayAccess implements \ArrayAccess
{
    private $container = [];

    public function __construct($array)
    {
        foreach ($array as $key => $value) {
            $this->container[$key] = $value;
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}

class AllTheThings implements \ArrayAccess
{
    public $foo  = 'fail';
    public $bar  = 'win';
    private $qux = 'fail';

    public function foo()
    {
        return 'win';
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'foo':
            case 'bar':
                return 'fail';

            case 'baz':
            case 'qux':
                return 'win';

            default:
                return 'lolwhut';
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        // nada
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        // nada
    }
}
