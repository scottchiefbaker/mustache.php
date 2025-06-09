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
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * @group functional
 * @group partials
 */
class NestedPartialIndentTest extends TestCase
{
    /**
     * @dataProvider partialsAndStuff
     */
    public function testNestedPartialsAreIndentedProperly($src, array $partials, $expected)
    {
        $m = new Engine([
            'partials' => $partials,
        ]);
        $tpl = $m->loadTemplate($src);
        $this->assertEquals($expected, $tpl->render());
    }

    public function partialsAndStuff()
    {
        $partials = [
            'a' => ' {{> b }}',
            'b' => ' {{> d }}',
            'c' => ' {{> d }}{{> d }}',
            'd' => 'D!',
        ];

        return [
            [' {{> a }}', $partials, '   D!'],
            [' {{> b }}', $partials, '  D!'],
            [' {{> c }}', $partials, '  D!D!'],
        ];
    }
}
