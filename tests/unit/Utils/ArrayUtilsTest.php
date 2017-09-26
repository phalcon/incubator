<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2017 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>             |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Test\Utils;

use Codeception\TestCase\Test;
use UnitTester;
use Phalcon\Utils\ArrayUtils;
use ArrayIterator;

class ArrayUtilsTest extends Test
{
    /**
     * Tests ArrayUtils::iteratorToArray. Testing array.
     *
     * @dataProvider providerArray
     * @param array $array
     * @param array $array
     *
     * @test
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-09-26
     */
    public function shouldReturnArrayFromArray($array, $expected)
    {
        $utils = new ArrayUtils();

        $this->assertEquals(
            $expected,
            $utils->iteratorToArray($array),
            'Arrays are different'
        );
    }

    /**
     * Tests ArrayUtils::iteratorToArray. Testing iterator.
     *
     * @dataProvider providerArray
     * @param array $array
     * @param array $array
     *
     * @test
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-09-26
     */
    public function shouldReturnArrayFromIterator($array, $expected)
    {
        $utils = new ArrayUtils();
        $iterator = new ArrayIterator($array);

        $this->assertEquals(
            $expected,
            $utils->iteratorToArray($iterator),
            'Arrays are different'
        );
    }

    public function providerArray()
    {
        return require INCUBATOR_FIXTURES . 'Utils/array_utils.php';
    }
}
