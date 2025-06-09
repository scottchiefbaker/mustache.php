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
class ClosureQuirksTest extends TestCase
{
    private $mustache;

    public function set_up()
    {
        $this->mustache = new Engine();
    }

    public function testClosuresDontLikeItWhenYouTouchTheirProperties()
    {
        $tpl = $this->mustache->loadTemplate('{{ foo.bar }}');
        $this->assertEquals('', $tpl->render(['foo' => function () {
            return 'FOO';
        }]));
    }
}
