<?php

namespace Phalcon\Mvc\Model\Behavior\Blameable;

use Phalcon\Http\Request;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Session\Adapter;

/**
 * Phalcon\Mvc\Model\Behavior\Blameable\Audit
 *
 * @package Phalcon\Mvc\Model\Behavior\Blameable
 */
class Audit extends Model implements AuditInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $user_name;

    /**
     * @var string
     */
    public $model_name;

    /**
     * @var string
     */
    public $ipaddress;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $created_at;

    /**
     * @var ModelInterface
     */
    public $model;

    /**
     * @var array
     */
    public $primary_key;

    /**
     * @var callable
     */
    public $userCallback;

    /**
     * Sets relations between models
     */
    public function initialize()
    {
        $this->hasMany('id', AuditDetail::class, 'audit_id', ['alias' => 'details']);
    }

    /**
     * Executes code to set audits all needed data, like ipaddress, username, created_at etc
     */
    public function beforeValidation()
    {
        if (empty($this->userCallback)) {
            /** @var Adapter $session */
            $session = $this->getDI()->get('session');

            //Get the username from session
            $this->user_name = $session->get('userName');
        } else {
            $userCallback = $this->userCallback;
            $this->user_name = $userCallback($this->getDI());
        }

        //The model who performed the action
        $this->model_name = get_class($this->model);

        /** @var Request $request */
        $request = $this->getDI()->get('request');

        //The client IP address
        $this->ipaddress = $request->getClientAddress();

        //Current time
        $this->created_at = date('Y-m-d H:i:s');

        $primaryKeys = $this->getModelsMetaData()->getPrimaryKeyAttributes($this->model);

        $columnMap = $this->getModelsMetaData()->getColumnMap($this->model);

        $primaryValues = [];
        if (!empty($columnMap)) {
            foreach ($primaryKeys as $primaryKey) {
                $primaryValues[] = $this->model->readAttribute($columnMap[$primaryKey]);
            }
        } else {
            foreach ($primaryKeys as $primaryKey) {
                $primaryValues[] = $this->model->readAttribute($primaryKey);
            }
        }

        $this->primary_key = json_encode($primaryValues);
    }

    public function afterSave()
    {
        $this->primary_key = json_decode($this->primary_key, true);
    }

    public function afterFetch()
    {
        $this->primary_key = json_decode($this->primary_key, true);
    }

    /**
     * @param ModelInterface $model
     * @return $this
     */
    public function setModel(ModelInterface $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param $userCallback
     * @return $this
     */
    public function setUserCallback($userCallback)
    {
        $this->userCallback = $userCallback;

        return $this;
    }
}
