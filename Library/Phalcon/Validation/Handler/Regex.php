<?php
namespace Phalcon\Validation\Handler
{

    /**
     * Equivalent to Filter(filter=FILTER_VALIDATE_REGEX)
     *
     * @author hu2008yinxiang@163.com
     *        
     */
    class Regex extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        protected function defaultParams()
        {
            return array(
                'trim' => true
            );
        }

        public function checkValid()
        {
            $context = $this->context;
            $regex = $this->params[0];
            
            $value = $this->params['trim'] ? trim($context->{$this->key}) : $context->{$this->key};
            
            return filter_var($value, FILTER_VALIDATE_REGEXP, array(
                'options' => array(
                    'regexp' => $regex
                )
            )) === false ? array(
                'valid' => false,
                'message' => $this->getMessage()
            ) : array(
                'valid' => true
            );
        }
    }
}