<?php

namespace Phalcon\Mvc\View;

use \Phalcon\Mvc\View;

class SmartyView extends View
{
    public function __construct()
    {
        parent::__construct();
    }

    public function setVar($key, $value, $nocache = false)
    {
        $this->_viewParams[$key] = $value;
        $this->_viewParams["_" . $key] = $nocache;
    }
}