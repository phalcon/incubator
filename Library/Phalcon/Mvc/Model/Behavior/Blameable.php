<?php

namespace Phalcon\Mvc\Model\Behavior;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\Behavior\Blameable\Audit;
use Phalcon\Mvc\Model\Behavior\Blameable\AuditDetail;
use Phalcon\Mvc\Model\Behavior\Blameable\AuditDetailInterface;
use Phalcon\Mvc\Model\Behavior\Blameable\AuditInterface;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * Phalcon\Mvc\Model\Behavior\Blameable
 */
class Blameable extends Behavior implements BehaviorInterface
{
    /**
     * @var array
     */
    protected $snapshot;

    /**
     * @var array
     */
    protected $changedFields;

    /**
     * @var string
     */
    protected $auditClass = Audit::class;

    /**
     * @var string
     */
    protected $auditDetailClass = AuditDetail::class;

    /**
     * @var callable
     */
    protected $userCallback;

    /**
     * @var boolean
     */
    protected $snapshotUpdatingDisabled = false;

    /**
     * Blameable constructor.
     * @param array|null $options
     * @throws Exception
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        if (isset($options['auditClass'])) {
            if (!in_array(AuditInterface::class, class_implements($options['auditClass']))) {
                throw new Exception(
                    "Your class must implement Phalcon\\Mvc\\Model\\Behavior\\Blameable\\AuditInterface"
                );
            }
            $this->auditClass = $options['auditClass'];
        }

        if (isset($options['auditDetailClass'])) {
            if (!in_array(AuditDetailInterface::class, class_implements($options['auditDetailClass']))) {
                throw new Exception(
                    "Your class must implement Phalcon\\Mvc\\Model\\Behavior\\Blameable\\AuditDetailInterface"
                );
            }
            $this->auditDetailClass = $options['auditDetailClass'];
        }

        if (isset($options['userCallback'])) {
            if (!is_callable($options['userCallback'])) {
                throw new Exception("User callback must be callable!");
            }
            $this->userCallback = $options['userCallback'];
        }

        if (isset($options['snapshotUpdatingDisabled'])) {
            $this->snapshotUpdatingDisabled = $options['snapshotUpdatingDisabled'];
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $eventType
     * @param \Phalcon\Mvc\ModelInterface $model
     */
    public function notify($eventType, ModelInterface $model)
    {
        //Fires 'auditAfterCreate' if the event is 'afterCreate'
        if ($eventType == 'afterCreate') {
            return $this->auditAfterCreate($model);
        }

        //Fires 'auditAfterUpdate' if the event is 'afterUpdate'
        if ($eventType == 'afterUpdate') {
            return $this->auditAfterUpdate($model);
        }

        // Fires 'collectData' if the event is 'beforeUpdate'
        if ($eventType == 'beforeUpdate' && $this->snapshotUpdatingDisabled) {
            return $this->collectData($model);
        }
    }

    /**
     * Creates an Audit isntance based on the current enviroment
     *
     * @param  string $type
     * @param  \Phalcon\Mvc\ModelInterface $model
     * @return AuditInterface
     */
    public function createAudit($type, ModelInterface $model)
    {
        $auditClass = $this->auditClass;
        /** @var AuditInterface $audit */
        $audit = new $auditClass();
        $audit->setUserCallback($this->userCallback);
        $audit->setModel($model);
        $audit->setType($type);

        return $audit;
    }

    /**
     * Audits an DELETE operation
     *
     * @param  \Phalcon\Mvc\ModelInterface $model
     * @return boolean
     */
    public function auditAfterCreate(ModelInterface $model)
    {
        /** @var AuditInterface|ModelInterface $audit */
        $audit = $this->createAudit('C', $model);
        /** @var Model\MetaData $metaData */
        $metaData = $model->getModelsMetaData();
        $fields = $metaData->getAttributes($model);
        $columnMap = $metaData->getColumnMap($model);
        $details = [];
        $auditDetailClass = $this->auditDetailClass;

        foreach ($fields as $field) {
            /** @var AuditDetailInterface $auditDetail */
            $auditDetail = new $auditDetailClass();
            $auditDetail->setOldValue(null);
            if (empty($columnMap)) {
                $auditDetail->setFieldName($field);
                $auditDetail->setNewValue($model->readAttribute($field));
            } else {
                $auditDetail->setFieldName($columnMap[$field]);
                $auditDetail->setNewValue($model->readAttribute($columnMap[$field]));
            }

            $details[] = $auditDetail;
        }

        $audit->details = $details;

        return $audit->save();
    }

    /**
     * Audits an UPDATE operation
     *
     * @param  \Phalcon\Mvc\ModelInterface $model
     * @return boolean
     */
    public function auditAfterUpdate(ModelInterface $model)
    {
        if ($this->snapshotUpdatingDisabled) {
            $changedFields = $this->changedFields;
        } else {
            $changedFields = $model->getUpdatedFields();
        }

        if (count($changedFields) == 0) {
            return null;
        }

        /** @var AuditInterface|ModelInterface $audit */
        $audit = $this->createAudit('U', $model);

        //Date the model had before modifications
        if ($this->snapshotUpdatingDisabled) {
            $originalData = $this->snapshot;
        } else {
            $originalData = $model->getOldSnapshotData();
        }
        $auditDetailClass = $this->auditDetailClass;

        $details = [];
        foreach ($changedFields as $field) {
            /** @var AuditDetailInterface $auditDetail */
            $auditDetail = new $auditDetailClass();
            $auditDetail->setFieldName($field);
            $auditDetail->setOldValue($originalData[$field]);
            $auditDetail->setNewValue($model->readAttribute($field));

            $details[] = $auditDetail;
        }

        $audit->details = $details;

        return $audit->save();
    }

    /**
     * @param ModelInterface $model
     */
    protected function collectData(ModelInterface $model)
    {
        $this->snapshot = $model->getSnapshotData();
        $this->changedFields = $model->getChangedFields();
    }
}
