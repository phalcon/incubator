<?php
namespace Phalcon\Validation\Handler
{

    class NotEmpty extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {
        
        /*
         * {@inheritdoc}
         * @see \Phalcon\Validation\AnnotationHandler::defaultParams()
         */
        protected function defaultParams()
        {
            return array(
                'trim' => true,
                'strict' => false,
                'message' => '{keyName} should not be empty!'
            );
        }
        
        /*
         * {@inheritdoc}
         * @see \Phalcon\Validation\AnnotationHandlerInterface::checkValid()
         */
        public function checkValid()
        {
            $context = $this->context;
            $value = $this->params['trim'] ? trim($context->{$this->key}) : $context->{$this->key};
            if ($this->params['strict']) {
                return strlen($value) ? array(
                    'valid' => true
                ) : array(
                    'valid' => false,
                    'message' => $this->getMessage()
                );
            } else {
                return empty($value) ? array(
                    'valid' => false,
                    'message' => $this->getMessage()
                ) : array(
                    'valid' => true
                );
            }
        }
    }
}