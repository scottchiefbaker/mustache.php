<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2025 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mustache\Test\Exception;

use Mustache\Exception\UnknownHelperException;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class UnknownHelperExceptionTest extends TestCase
{
    public function testInstance()
    {
        $e = new UnknownHelperException('alpha');
        $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        $this->assertInstanceOf(\Mustache\Exception::class, $e);
    }

    public function testMessage()
    {
        $e = new UnknownHelperException('beta');
        $this->assertEquals('Unknown helper: beta', $e->getMessage());
    }

    public function testGetHelperName()
    {
        $e = new UnknownHelperException('gamma');
        $this->assertEquals('gamma', $e->getHelperName());
    }

    public function testPrevious()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Exception chaining requires at least PHP 5.3');
        }

        $previous = new \Exception();
        $e = new UnknownHelperException('foo', $previous);
        $this->assertSame($previous, $e->getPrevious());
    }
}
