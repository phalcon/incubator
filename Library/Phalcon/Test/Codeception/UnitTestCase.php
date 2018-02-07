<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Phoenix Osiris <phoenix@twistersfury.com>                     |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Test\Codeception;

use Codeception\Test\Unit;

use Phalcon\Test\Traits\UnitTestCase as UnitTestCaseTrait;
use UnitTester;

class UnitTestCase extends Unit
{
    use UnitTestCaseTrait;


    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    /**
     * Standard Setup Method For PHPUnit. Calling setUpPhalcon Here to Maintain Codeception's _before Without a call
     * to parent::_before
     */
    protected function setUp()
    {
        $this->setUpPhalcon();
        parent::setUp();
    }

    /**
     * Standard Tear Down Method For PHPUnit. Calling tearDownPhalcon Here to Maintain Codeception's _after
     * Without a call to parent::_before
     */
    protected function tearDown()
    {
        $this->tearDownPhalcon();
        parent::tearDown();
    }
}
