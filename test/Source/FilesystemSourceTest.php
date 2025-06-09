<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mustache\Test\Source;

use Mustache\Source\FilesystemSource;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * @group unit
 */
class FilesystemSourceTest extends TestCase
{
    public function testMissingTemplateThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $source = new FilesystemSource(__DIR__ . '/not_a_file.mustache', ['mtime']);
        $source->getKey();
    }
}
