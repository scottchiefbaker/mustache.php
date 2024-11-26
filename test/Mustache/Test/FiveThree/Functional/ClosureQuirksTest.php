<?php

/*
 * This file is part of Mustache.php.
 *
 * (c) 2010-2017 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @group lambdas
 * @group functional
 */
class Mustache_Test_FiveThree_Functional_ClosureQuirksTest extends Yoast\PHPUnitPolyfills\TestCases\TestCase
{
    private $mustache;

    public function set_up()
    {
        $this->mustache = new Mustache_Engine();
    }

    public function testClosuresDontLikeItWhenYouTouchTheirProperties()
    {
        $tpl = $this->mustache->loadTemplate('{{ foo.bar }}');
        $this->assertEquals('', $tpl->render(array('foo' => function () {
            return 'FOO';
        })));
    }
}
