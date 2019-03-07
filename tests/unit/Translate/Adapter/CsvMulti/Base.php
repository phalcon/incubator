<?php

namespace Phalcon\Test\Unit\Translate\Adapter\CsvMulti;

use Phalcon\Translate\Adapter\CsvMulti;

/**
 * Class GetIndexesCest
 */
abstract class Base
{
    
    protected $adapter;
    
    public function _before()
    {
        $content = dirname(__FILE__) . '/../../../../_data/assets/translation/csv/names.csv';
        $params = ['content' => $content];
        $this->adapter = new CsvMulti($params);
    }
    
    public function _after()
    {
        $this->adapter = null;
    }
    
}