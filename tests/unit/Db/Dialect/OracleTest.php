<?php

namespace Phalcon\Test\Db\Dialect;

use UnitTester;
use Codeception\TestCase\Test;
// @todo Must be renamed to 'Phalcon\Db\Dialect\Oracle' after removing Oracle dialect from Phalcon
use Phalcon\Db\Dialect\OracleExtended;

/**
 * \Phalcon\Test\Db\Dialect\OracleTest
 * Tests for Phalcon\Db\Dialect\Oracle dialect
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @package   Phalcon\Test\Db\Dialect
 * @group     db
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class OracleTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    public function testDescribeColumnsForSchemaWithDots()
    {
        $dialect = new OracleExtended();

        $sql = $dialect->describeColumns('table', 'database.name.with.dots');

        $expected = [
            'SELECT TC.COLUMN_NAME, TC.DATA_TYPE, TC.DATA_LENGTH, TC.DATA_PRECISION, TC.DATA_SCALE, TC.NULLABLE, ',
            'C.CONSTRAINT_TYPE, TC.DATA_DEFAULT, CC.POSITION FROM ALL_TAB_COLUMNS TC LEFT JOIN (ALL_CONS_COLUMNS CC ',
            'JOIN ALL_CONSTRAINTS C ON (CC.CONSTRAINT_NAME = C.CONSTRAINT_NAME AND CC.TABLE_NAME = C.TABLE_NAME ',
            "AND CC.OWNER = C.OWNER AND C.CONSTRAINT_TYPE = 'P')) ON TC.TABLE_NAME = CC.TABLE_NAME AND ",
            "TC.COLUMN_NAME = CC.COLUMN_NAME WHERE TC.TABLE_NAME = 'TABLE' AND ",
            "TC.OWNER = 'DATABASE.NAME.WITH.DOTS' ORDER BY TC.COLUMN_ID"
        ];

        $this->assertEquals(join('', $expected), $sql);
    }
}
