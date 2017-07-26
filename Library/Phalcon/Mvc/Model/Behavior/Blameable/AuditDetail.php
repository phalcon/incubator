<?php

namespace Phalcon\Mvc\Model\Behavior\Blameable;

use Phalcon\Mvc\Model;

/**
 * Class AuditDetail
 * @package Phalcon\Mvc\Model\Behavior\Blameable
 */
class AuditDetail extends Model implements AuditDetailInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $field_name;

    /**
     * @var string
     */
    public $old_value;

    /**
     * @var string
     */
    public $new_value;

    /**
     * Sets relations between models
     */
    public function initialize()
    {
        $this->belongsTo('audit_id', Audit::class, 'id', ['alias' => 'audit']);
    }

    /**
     * @param string $fieldName
     * @return $this
     */
    public function setFieldName($fieldName)
    {
        $this->field_name = $fieldName;

        return $this;
    }

    /**
     * @param string $oldValue
     * @return $this
     */
    public function setOldValue($oldValue)
    {
        $this->old_value = $oldValue;

        return $this;
    }

    /**
     * @param string $newValue
     * @return $this
     */
    public function setNewValue($newValue)
    {
        $this->new_value = $newValue;

        return $this;
    }
}
