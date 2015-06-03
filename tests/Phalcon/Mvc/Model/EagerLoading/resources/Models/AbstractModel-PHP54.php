<?php namespace EagerLoadingTestModel;

abstract class AbstractModel extends \Phalcon\Mvc\Model
{
    use \Phalcon\Mvc\Model\EagerLoadingTrait;
}
