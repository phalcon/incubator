<?php

namespace Phalcon\Mvc\Model\Behavior\Blameable;

use Phalcon\Mvc\ModelInterface;

/**
 * Interface AuditInterface
 * @package Phalcon\Mvc\Model\Behavior\Blameable
 */
interface AuditInterface
{
    /**
     * Executes code to set audits all needed data, like ipaddress, username, created_at etc
     */
    public function beforeValidation();

    /**
     * Sets model to be used in audit
     *
     * @param ModelInterface $model
     * @return AuditInterface
     */
    public function setModel(ModelInterface $model);

    /**
     * Sets type of audit, C - Create, U - Update
     *
     * @param string $type
     * @return AuditInterface
     */
    public function setType($type);

    /**
     * Sets relations between models
     *
     * There should be relation hasMany with alias details pointing to AuditDetailInterface
     */
    public function initialize();

    /**
     * Sets user callback
     *
     * @param $userCallback
     * @return mixed
     */
    public function setUserCallback($userCallback);
}
