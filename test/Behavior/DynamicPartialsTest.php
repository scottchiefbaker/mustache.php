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
use Mustache\Exception\SyntaxException;
use Mustache\Test\TestCase;

class DynamicPartialsTest extends TestCase
{
    private $mustache;

    public function set_up()
    {
        $this->mustache = new Engine([
            'pragmas' => [Engine::PRAGMA_DYNAMIC_NAMES],
        ]);
    }

    public function getValidDynamicNamesExamples()
    {
        // technically not all dynamic names, but also not invalid
        return [
            ['{{>* foo }}'],
            ['{{>* foo.bar.baz }}'],
            ['{{=* *=}}'],
            ['{{! *foo }}'],
            ['{{! foo.*bar }}'],
            ['{{% FILTERS }}{{! foo | *bar }}'],
            ['{{% BLOCKS }}{{< *foo }}{{/ *foo }}'],
        ];
    }

    /**
     * @dataProvider getValidDynamicNamesExamples
     */
    public function testLegalInheritanceExamples($template)
    {
        $this->assertSame('', $this->mustache->render($template));
    }

    public function getDynamicNameParseErrors()
    {
        return [
            ['{{# foo }}{{/ *foo }}'],
            ['{{^ foo }}{{/ *foo }}'],
            ['{{% BLOCKS }}{{< foo }}{{/ *foo }}'],
            ['{{% BLOCKS }}{{$ foo }}{{/ *foo }}'],
        ];
    }

    /**
     * @dataProvider getDynamicNameParseErrors
     */
    public function testDynamicNameParseErrors($template)
    {
        $this->expectException(SyntaxException::class);
        $this->expectExceptionMessage('Nesting error:');
        $this->mustache->render($template);
    }

    public function testDynamicBlocks()
    {
        $tpl = '{{% BLOCKS }}{{< *partial }}{{$ bar }}{{ value }}{{/ bar }}{{/ *partial }}';

        $this->mustache->setPartials([
            'foobarbaz' => '{{% BLOCKS }}{{$ foo }}foo{{/ foo }}{{$ bar }}bar{{/ bar }}{{$ baz }}baz{{/ baz }}',
            'qux' => 'qux',
        ]);

        $result = $this->mustache->render($tpl, [
            'partial' => 'foobarbaz',
            'value' => 'BAR',
        ]);

        $this->assertSame($result, 'fooBARbaz');
    }
}
