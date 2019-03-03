<?php
declare(strict_types=1);

namespace Phalcon\Test\Unit\Translate\Adapter\CsvMulti;

use ArrayAccess;
use Phalcon\Translate\Adapter\CsvMulti;
use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Exception;
use UnitTester;

/**
 * Class QueryCest
 */
class ExistsCest
{
    public function translateAdapterExistsNoLocaleSet(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - exists cannot work without a locale having been set');
        $I->expectThrowable(
            new Exception('The locale must have been defined.'),
            function () {
                $content = dirname(__FILE__) . '/../../../../_data/assets/translation/csv/names.csv';
                $params = ['content' => $content];
                $adapter = new CsvMulti($params);
                $adapter->exists('label_street');
            }
        );
    }
    
    public function translateAdapterExistsItDoesnt(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - exists returns false');
        $content = dirname(__FILE__) . '/../../../../_data/assets/translation/csv/names.csv';
        $params = ['content' => $content];
        $adapter = new CsvMulti($params);
        $adapter->setLocale('en_US');
        $I->assertFalse($adapter->exists('label_cat'));
    }
    
    public function translateAdapterExistsItDoes(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - exists returns true');
        $content = dirname(__FILE__) . '/../../../../_data/assets/translation/csv/names.csv';
        $params = ['content' => $content];
        $adapter = new CsvMulti($params);
        $adapter->setLocale('en_US');
        $I->assertTrue($adapter->exists('label_street'));
        $I->assertTrue($adapter->exists('label_car'));
        $I->assertTrue($adapter->exists('label_home'));
    }
}
