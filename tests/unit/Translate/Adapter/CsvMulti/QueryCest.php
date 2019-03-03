<?php
declare(strict_types=1);

namespace Phalcon\Test\Unit\Translate\Adapter\CsvMulti;

use Phalcon\Translate\Adapter\CsvMulti;
use UnitTester;

/**
 * Class QueryCest
 */
class QueryCest
{
    public function queryNoTranslationMode(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - query returns the key when locale is false');
        $content = dirname(__FILE__) . '/../../../../_data/assets/translation/csv/names.csv';
        $params = ['content' => $content];
        $adapter = new CsvMulti($params);
        $adapter->setLocale(false);
        $I->assertEquals('label_street', $adapter->query('label_street'));
        $I->assertEquals('label_street', $adapter->query('label_street', 'placeholder_for_street'));
    }
    
    public function queryDoesTranslate(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - query returns the translated string matching the index');
        $content = dirname(__FILE__) . '/../../../../_data/assets/translation/csv/names.csv';
        $params = ['content' => $content];
        $adapter = new CsvMulti($params);
        $adapter->setLocale('en_US');
        $I->assertEquals('street', $adapter->query('label_street'));
        $I->assertEquals('car', $adapter->query('label_car'));
        $I->assertEquals('home', $adapter->query('label_home'));
        $adapter->setLocale('fr_FR');
        $I->assertEquals('rue', $adapter->query('label_street'));
        $I->assertEquals('voiture', $adapter->query('label_car'));
        $I->assertEquals('maison', $adapter->query('label_home'));
        $adapter->setLocale('es_ES');
        $I->assertEquals('calle', $adapter->query('label_street'));
        $I->assertEquals('coche', $adapter->query('label_car'));
        $I->assertEquals('casa', $adapter->query('label_home'));
    }
    
    public function queryKeyDoesntExist(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - query raises an exception when the key doesn\'t match and there is no placeholder');
        $I->expectThrowable(
            new \Exception("They key 'label_unexists' was not found."),
            function () {
                $content = dirname(__FILE__) . '/../../../../_data/assets/translation/csv/names.csv';
                $params = ['content' => $content];
                $adapter = new CsvMulti($params);
                $adapter->setLocale('en_US');
                $adapter->query('label_unexists');
            }
        );
    }
}
