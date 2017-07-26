<?php

namespace Phalcon\Test\Behavior\Blameable;

use Phalcon\Mvc\Model;

class Audit extends Model\Behavior\Blameable\Audit
{
    public function beforeValidation()
    {
        //Get the username from session
        $this->user_name = 'admin';

        //The model who performed the action
        $this->model_name = get_class($this->model);

        //The client IP address
        $this->ipaddress = '127.0.0.1';

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
}
