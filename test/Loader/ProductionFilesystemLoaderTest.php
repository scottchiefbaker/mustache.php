<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mustache\Test\Loader;

use Mustache\Exception\RuntimeException;
use Mustache\Exception\UnknownTemplateException;
use Mustache\Loader\ProductionFilesystemLoader;
use Mustache\Source;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * @group unit
 */
class ProductionFilesystemLoaderTest extends TestCase
{
    public function testConstructor()
    {
        $baseDir = realpath(__DIR__ . '/../fixtures/templates');
        $loader = new ProductionFilesystemLoader($baseDir, ['extension' => '.ms']);
        $this->assertInstanceOf(Source::class, $loader->load('alpha'));
        $this->assertEquals('alpha contents', $loader->load('alpha')->getSource());
        $this->assertInstanceOf(Source::class, $loader->load('beta.ms'));
        $this->assertEquals('beta contents', $loader->load('beta.ms')->getSource());
    }

    public function testTrailingSlashes()
    {
        // Not realpath, because it strips trailing slashes
        $baseDir = __DIR__ . '/../fixtures/templates/';
        $loader = new ProductionFilesystemLoader($baseDir);
        $this->assertEquals('one contents', $loader->load('one')->getSource());
    }

    public function testConstructorWithProtocol()
    {
        $baseDir = realpath(__DIR__ . '/../fixtures/templates');

        $loader = new ProductionFilesystemLoader('file://' . $baseDir, ['extension' => '.ms']);
        $this->assertEquals('alpha contents', $loader->load('alpha')->getSource());
        $this->assertEquals('beta contents', $loader->load('beta.ms')->getSource());
    }

    public function testLoadTemplates()
    {
        $baseDir = realpath(__DIR__ . '/../fixtures/templates');
        $loader = new ProductionFilesystemLoader($baseDir);
        $this->assertEquals('one contents', $loader->load('one')->getSource());
        $this->assertEquals('two contents', $loader->load('two.mustache')->getSource());
    }

    public function testEmptyExtensionString()
    {
        $baseDir = realpath(__DIR__ . '/../fixtures/templates');

        $loader = new ProductionFilesystemLoader($baseDir, ['extension' => '']);
        $this->assertEquals('one contents', $loader->load('one.mustache')->getSource());
        $this->assertEquals('alpha contents', $loader->load('alpha.ms')->getSource());

        $loader = new ProductionFilesystemLoader($baseDir, ['extension' => null]);
        $this->assertEquals('two contents', $loader->load('two.mustache')->getSource());
        $this->assertEquals('beta contents', $loader->load('beta.ms')->getSource());
    }

    public function testMissingBaseDirThrowsException()
    {
        $this->expectException(RuntimeException::class);
        new ProductionFilesystemLoader(__DIR__ . '/not_a_directory');
    }

    public function testMissingTemplateThrowsException()
    {
        $this->expectException(UnknownTemplateException::class);
        $baseDir = realpath(__DIR__ . '/../fixtures/templates');
        $loader = new ProductionFilesystemLoader($baseDir);

        $loader->load('fake');
    }

    public function testLoadWithDifferentStatProps()
    {
        $baseDir = realpath(__DIR__ . '/../fixtures/templates');
        $noStatLoader = new ProductionFilesystemLoader($baseDir, ['stat_props' => null]);
        $mtimeLoader = new ProductionFilesystemLoader($baseDir, ['stat_props' => ['mtime']]);
        $sizeLoader = new ProductionFilesystemLoader($baseDir, ['stat_props' => ['size']]);
        $bothLoader = new ProductionFilesystemLoader($baseDir, ['stat_props' => ['mtime', 'size']]);

        $noStatKey = $noStatLoader->load('one.mustache')->getKey();
        $mtimeKey = $mtimeLoader->load('one.mustache')->getKey();
        $sizeKey = $sizeLoader->load('one.mustache')->getKey();
        $bothKey = $bothLoader->load('one.mustache')->getKey();

        $this->assertNotEquals($noStatKey, $mtimeKey);
        $this->assertNotEquals($noStatKey, $sizeKey);
        $this->assertNotEquals($noStatKey, $bothKey);
        $this->assertNotEquals($mtimeKey, $sizeKey);
        $this->assertNotEquals($mtimeKey, $bothKey);
        $this->assertNotEquals($sizeKey, $bothKey);
    }
}
