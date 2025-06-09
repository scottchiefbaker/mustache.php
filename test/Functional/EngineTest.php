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
 * @group pragmas
 * @group functional
 */
class EngineTest extends TestCase
{
    /**
     * @dataProvider pragmaData
     */
    public function testPragmasConstructorOption($pragmas, $helpers, $data, $tpl, $expect)
    {
        $mustache = new Engine([
            'pragmas' => $pragmas,
            'helpers' => $helpers,
        ]);

        $this->assertEquals($expect, $mustache->render($tpl, $data));
    }

    public function pragmaData()
    {
        $helpers = [
            'longdate' => function (\DateTime $value) {
                return $value->format('Y-m-d h:m:s');
            },
        ];

        $data = [
            'date' => new \DateTime('1/1/2000', new \DateTimeZone('UTC')),
        ];

        $tpl = '{{ date | longdate }}';

        return [
            [[Engine::PRAGMA_FILTERS], $helpers, $data, $tpl, '2000-01-01 12:01:00'],
            [[],                       $helpers, $data, $tpl, ''],
        ];
    }
}
