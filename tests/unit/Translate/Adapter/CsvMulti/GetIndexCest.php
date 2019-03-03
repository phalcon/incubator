<?php
declare(strict_types=1);

namespace Phalcon\Test\Unit\Translate\Adapter\CsvMulti;

// TODO : autoload instead of require_once ? see https://stackoverflow.com/questions/35386276/codeception-cest-inheritance
require_once 'Base.php';
use Phalcon\Test\Unit\Translate\Adapter\CsvMulti\Base;

use UnitTester;

/**
 * Class GetIndexesCest
 */
class GetIndexesCest extends Base
{
    public function getIndexes(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\CsvMulti - getIndexes returns the indexes');
        $this->adapter->setLocale('en_US');
        $I->assertEquals(['label_street', 'label_car', 'label_home'], $this->adapter->getIndexes());
    }
}
