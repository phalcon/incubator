<?php

namespace Phalcon\Mvc\Model\Behavior\Blameable;

/**
 * Interface AuditDetailInterface
 * @package Phalcon\Mvc\Model\Behavior\Blameable
 */
interface AuditDetailInterface
{
    /**
     * @param string $fieldName
     * @return AuditDetailInterface
     */
    public function setFieldName($fieldName);

    /**
     * @param string $oldValue
     * @return AuditDetailInterface
     */
    public function setOldValue($oldValue);

    /**
     * @param string $newValue
     * @return AuditDetailInterface
     */
    public function setNewValue($newValue);

    /**
     * Sets relations between models
     *
     * There should be relation belongsTo with alias name audit pointing to AuditInterface
     */
    public function initialize();
}
