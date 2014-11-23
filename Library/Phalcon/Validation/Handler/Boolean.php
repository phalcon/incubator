<?php
namespace Phalcon\Validation\Handler
{

    /**
     * Equivalent to Filter(filter=FILTER_VALIDATE_BOOLEAN)
     * 
     * @author hu2008yinxiang@163.com
     *        
     */
    class Boolean extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        protected function defaultParams()
        {
            return array(
                'message' => '{keyName} should be true or false.'
            );
        }

        public function checkValid()
        {
            $value = $this->context->{$this->key};
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) === null ? array(
                'valid' => false,
                'message' => $this->getMessage()
            ) : array(
                'valid' => true
            );
        }
    }
}