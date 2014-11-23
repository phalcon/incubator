<?php
namespace Phalcon\Validation\Handler
{

    /**
     * Equivalent to Filter(filter=FILTER_VALIDATE_FLOAD,flag=FILTER_FLAG_ALLOW_THOUSAND)
     * @author hu2008yinxiang@163.com
     *
     */
    class Number extends Filter implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        protected function defaultParams()
        {
            return array_merge(parent::defaultParams(), array(
                'filter' => FILTER_VALIDATE_FLOAT,
                'flag' => FILTER_FLAG_ALLOW_THOUSAND,
                'message' => '{keyName} should be a number.'
            ));
        }
    }
}