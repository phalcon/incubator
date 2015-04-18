<?php
namespace Phalcon\Mvc\Model\Behavior;

use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * Phalcon\Mvc\Model\Behavior\Blameable
 */
class Blameable extends Behavior implements BehaviorInterface
{

    /**
     * Class constructor.
     *
     * @param array $options
     */
    public function __construct($options = null)
    {
        $this->_options = $options;
    }

    /**
     * {@inheritdoc}
     *
     * @param string                      $eventType
     * @param \Phalcon\Mvc\ModelInterface $model
     */
    public function notify($eventType, $model)
    {
        //Fires 'logAfterUpdate' if the event is 'afterCreate'
        if ($eventType == 'afterCreate') {
            return $this->auditAfterCreate($model);
        }

        //Fires 'logAfterUpdate' if the event is 'afterUpdate'
        if ($eventType == 'afterUpdate') {
            return $this->auditAfterUpdate($model);
        }
    }

    /**
     * Creates an Audit isntance based on the current enviroment
     *
     * @param  string                      $type
     * @param  \Phalcon\Mvc\ModelInterface $model
     * @return Audit
     */
    public function createAudit($type, ModelInterface $model)
    {
        //Get the session service
        $session = $model->getDI()->getSession();

        //Get the request service
        $request = $model->getDI()->getRequest();

        $audit = new Audit();

        //Get the username from session
        $audit->user_name = $session->get('userName');

        //The model who performed the action
        $audit->model_name = get_class($model);

        //The client IP address
        $audit->ipaddress = $request->getClientAddress();

        //Action is an update
        $audit->type = $type;

        //Current time
        $audit->created_at = date('Y-m-d H:i:s');

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
        //Create a new audit
        $audit    = $this->createAudit('C', $model);
        $metaData = $model->getModelsMetaData();
        $fields   = $metaData->getAttributes($model);
        $details  = array();

        foreach ($fields as $field) {
            $auditDetail = new AuditDetail();
            $auditDetail->field_name = $field;
            $auditDetail->old_value = null;
            $auditDetail->new_value = $model->readAttribute($field);

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
        $changedFields = $model->getChangedFields();

        if (count($changedFields) == 0) {
            return null;
        }

        //Create a new audit
        $audit = $this->createAudit('U', $model);

        //Date the model had before modifications
        $originalData = $model->getSnapshotData();

        $details = array();
        foreach ($changedFields as $field) {
            $auditDetail = new AuditDetail();
            $auditDetail->field_name = $field;
            $auditDetail->old_value = $originalData[$field];
            $auditDetail->new_value = $model->readAttribute($field);

            $details[] = $auditDetail;
        }

        $audit->details = $details;

        return $audit->save();
    }
}
