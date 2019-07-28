<?php namespace Phalcon\Mvc\Model\EagerLoading;

use Phalcon\Mvc\Model\Query\Builder;

final class QueryBuilder extends Builder
{
    const E_NOT_ALLOWED_METHOD_CALL = 'When eager loading relations queries must return full entities';
    
    public function distinct($distinct)
    {
        throw new \LogicException(
            static::E_NOT_ALLOWED_METHOD_CALL
        );
    }

    public function columns($columns)
    {
        throw new \LogicException(
            static::E_NOT_ALLOWED_METHOD_CALL
        );
    }

    public function where($conditions, $bindParams = null, $bindTypes = null)
    {
        $currentConditions = $this->_conditions;

        /**
         * Nest the condition to current ones or set as unique
         */
        if ($currentConditions) {
            $conditions = "(" . $currentConditions . ") AND (" . $conditions . ")";
        }

        return parent::where($conditions, $bindParams, $bindTypes);
    }
}
