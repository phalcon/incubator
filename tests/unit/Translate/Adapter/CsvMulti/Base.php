<?php

namespace Phalcon\Test\Unit\Translate\Adapter\CsvMulti;

use Phalcon\Translate\Adapter\CsvMulti;

abstract class Base
{
    protected $adapter;
    
    public function _before()
    {
        $content = __DIR__ . '/../../../../_data/assets/translation/csv/names.csv';

        $params = [
            'content' => $content,
        ];

        $this->adapter = new CsvMulti($params);
    }

    public function _after()
    {
        $this->adapter = null;
    }
}
