<?php
namespace Phalcon\Validation\Handler
{

    /**
     * validate the value of current field is the specified value.
     * <code>
     * @Identical('yes')
     * public $agree=null;
     * </code>
     * @author hu2008yinxiang@163.com
     *        
     */
    class Identical extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        protected function defaultParams()
        {
            return array(
                'message' => 'The {keyName} should be \'{0}\'.'
            );
        }

        public function checkValid()
        {
            $param = $this->params[0]; // the first unnamed param
            $value = $this->context->{$this->key};
            return ($param == $value) ? array(
                'valid' => true
            ) : array(
                'valid' => false,
                'message' => $this->getMessage()
            );
        }

        protected function getPlaceHolders()
        {
            return array_merge(parent::getPlaceHolders(), array(
                '{0}' => $this->params[0]
            ));
        }
    }
}