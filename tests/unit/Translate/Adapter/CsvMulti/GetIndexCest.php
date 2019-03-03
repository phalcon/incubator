<?php
declare(strict_types=1);

namespace Phalcon\Test\Unit\Translate\Adapter\CsvMulti;

use Phalcon\Translate\Adapter\CsvMulti;
use UnitTester;

/**
 * Class GetIndexesCest
 */
class GetIndexesCest
{
    public function getIndexes(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - getIndexes returns the indexes');
        $content = dirname(__FILE__) . '/../../../../_data/assets/translation/csv/names.csv';
        $params = ['content' => $content];
        $adapter = new CsvMulti($params);
        $adapter->setLocale('en_US');
        $I->assertEquals(['label_street', 'label_car', 'label_home'], $adapter->getIndexes());
    }
}
