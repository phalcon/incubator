<?php

namespace Phalcon\Test\Unit\Translate\Adapter\CsvMulti;

// TODO : autoload instead of require_once ? see https://stackoverflow.com/questions/35386276/codeception-cest-inheritance
require_once 'Base.php';
use Phalcon\Test\Unit\Translate\Adapter\CsvMulti\Base;

use Phalcon\Translate\Exception;
use UnitTester;

/**
 * Class ExistsCest
 */
class ExistsCest extends Base
{
    public function translateAdapterExistsNoLocaleSet(UnitTester $I)
    {
        $I->wantToTest(
            'Translate\Adapter\CsvMulti - exists cannot work without a locale having been set'
        );

        $I->expectThrowable(
            new Exception('The locale must have been defined.'),
            function () {
                $this->adapter->exists('label_street');
            }
        );
    }
    
    public function translateAdapterExistsItDoesnt(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - exists returns false');

        $this->adapter->setLocale('en_US');

        $I->assertFalse(
            $this->adapter->exists('label_cat')
        );
    }
    
    public function translateAdapterExistsItDoes(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - exists returns true');

        $this->adapter->setLocale('en_US');

        $I->assertTrue(
            $this->adapter->exists('label_street')
        );

        $I->assertTrue(
            $this->adapter->exists('label_car')
        );

        $I->assertTrue(
            $this->adapter->exists('label_home')
        );
    }
}
