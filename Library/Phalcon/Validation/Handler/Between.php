<?php
namespace Phalcon\Validation\Handler
{

    /**
     * A between validation (both side is included).
     * 
     * @author hu2008yinxiang@163.com
     *        
     */
    class Between extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        protected function defaultParams()
        {
            return array(
                'min' => 0,
                'max' => 0,
                'message' => '{keyName} should between {min} and {max}.'
            );
        }

        public function checkValid()
        {
            $value = $this->context->{$this->key};
            return ($value > $this->params['max'] || $value < $this->params['min']) ? array(
                'valid' => false,
                'message' => $this->getMessage()
            ) : array(
                'valid' => true
            );
        }

        protected function getPlaceHolders()
        {
            return array_merge(parent::getPlaceHolders(), array(
                '{min}' => $this->params['min'],
                '{max}' => $this->params['max']
            ));
        }
    }
}