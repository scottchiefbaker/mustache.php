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
class StrictCallablesTest extends TestCase
{
    /**
     * @dataProvider callables
     */
    public function testStrictCallables($strict, $name, $section, $expected)
    {
        $mustache = new Engine(['strict_callables' => $strict]);
        $tpl      = $mustache->loadTemplate('{{# section }}{{ yourname }}{{/ section }}');

        $data = new \StdClass();
        $data->yourname    = $name;
        $data->section = $section;

        $this->assertEquals($expected, $tpl->render($data));
    }

    public function callables()
    {
        $lambda = function ($tpl, $mustache) {
            return strtoupper($mustache->render($tpl));
        };

        return [
            // Interpolation lambdas
            [
                false,
                [$this, 'instanceName'],
                $lambda,
                'YOSHI',
            ],
            [
                false,
                [__CLASS__, 'staticName'],
                $lambda,
                'YOSHI',
            ],
            [
                false,
                function () {
                    return 'Yoshi';
                },
                $lambda,
                'YOSHI',
            ],

            // Section lambdas
            [
                false,
                'Yoshi',
                [$this, 'instanceCallable'],
                'YOSHI',
            ],
            [
                false,
                'Yoshi',
                [__CLASS__, 'staticCallable'],
                'YOSHI',
            ],
            [
                false,
                'Yoshi',
                $lambda,
                'YOSHI',
            ],

            // Strict interpolation lambdas
            [
                true,
                function () {
                    return 'Yoshi';
                },
                $lambda,
                'YOSHI',
            ],

            // Strict section lambdas
            [
                true,
                'Yoshi',
                [$this, 'instanceCallable'],
                'YoshiYoshi',
            ],
            [
                true,
                'Yoshi',
                [__CLASS__, 'staticCallable'],
                'YoshiYoshi',
            ],
            [
                true,
                'Yoshi',
                function ($tpl, $mustache) {
                    return strtoupper($mustache->render($tpl));
                },
                'YOSHI',
            ],
        ];
    }

    public function instanceCallable($tpl, $mustache)
    {
        return strtoupper($mustache->render($tpl));
    }

    public static function staticCallable($tpl, $mustache)
    {
        return strtoupper($mustache->render($tpl));
    }

    public function instanceName()
    {
        return 'Yoshi';
    }

    public static function staticName()
    {
        return 'Yoshi';
    }
}
