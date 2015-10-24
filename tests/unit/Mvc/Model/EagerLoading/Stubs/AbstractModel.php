<?php

namespace Phalcon\Test\Mvc\Model\EagerLoading\Stubs;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\EagerLoadingTrait;

abstract class AbstractModel extends Model
{
    use EagerLoadingTrait;
}
