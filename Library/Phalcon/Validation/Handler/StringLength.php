<?php
namespace Phalcon\Validation\Handler
{

    class StringLength extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        protected function defaultParams()
        {
            return array(
                'min' => 0,
                'max' => PHP_INT_MAX,
                'trim' => true,
                'message' => 'The length of {keyName} should between {min} and {max}.'
            );
        }

        public function checkValid()
        {
            $value = $this->context->{$this->key};
            if ($this->params['trim']) {
                $value = trim($value);
            }
            $length = strlen($value);
            if ($length < $this->params['min'] || $length > $this->params['max']) {
                return array(
                    'valid' => false,
                    'message' => $this->getMessage()
                );
            }
            
            return array(
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