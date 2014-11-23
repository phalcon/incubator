<?php
namespace Phalcon\Validation\Handler
{

    /**
     * Equivalent to Filter(FILTER_VALIDATE_EMAIL)
     *
     * @see \Phalcon\Validation\Handler\Filter
     * @author hu2008yinxiang@163.com
     *        
     */
    class Email extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        protected function defaultParams()
        {
            return array(
                'message' => 'The {value} is not a valid email.'
            );
        }

        public function checkValid()
        {
            $value = $this->context->{$this->key};
            return (filter_var($value, FILTER_VALIDATE_EMAIL) === false) ? array(
                'valid' => false,
                'message' => $this->getMessage()
            ) : array(
                'valid' => true
            );
        }
    }
}