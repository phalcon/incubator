<?php
declare(strict_types=1);

namespace Phalcon\Test\Unit\Translate\Adapter\CsvMulti;

// TODO : autoload instead of require_once ? see https://stackoverflow.com/questions/35386276/codeception-cest-inheritance
require_once 'Base.php';
use Phalcon\Test\Unit\Translate\Adapter\CsvMulti\Base;

use Phalcon\Translate\Exception;
use UnitTester;

/**
 * Class QueryCest
 */
class QueryCest extends Base
{
    
    public function queryNoTranslationMode(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - query returns the key when locale is false');
        $this->adapter->setLocale(false);
        $I->assertEquals('label_street', $this->adapter->query('label_street'));
        $I->assertEquals('label_street', $this->adapter->query('label_street', 'placeholder_for_street'));
    }
    
    public function queryDoesTranslate(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - query returns the translated string matching the index');
        $this->adapter->setLocale('en_US');
        $I->assertEquals('street', $this->adapter->query('label_street'));
        $I->assertEquals('car', $this->adapter->query('label_car'));
        $I->assertEquals('home', $this->adapter->query('label_home'));
        $this->adapter->setLocale('fr_FR');
        $I->assertEquals('rue', $this->adapter->query('label_street'));
        $I->assertEquals('voiture', $this->adapter->query('label_car'));
        $I->assertEquals('maison', $this->adapter->query('label_home'));
        $this->adapter->setLocale('es_ES');
        $I->assertEquals('calle', $this->adapter->query('label_street'));
        $I->assertEquals('coche', $this->adapter->query('label_car'));
        $I->assertEquals('casa', $this->adapter->query('label_home'));
    }
    
    public function queryKeyDoesntExist(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - query raises an exception when the key doesn\'t match');
        $I->expectThrowable(
            new Exception("They key 'label_unexists' was not found."),
            function () {
                $this->adapter->setLocale('en_US');
                $this->adapter->query('label_unexists');
            }
        );
    }
}
