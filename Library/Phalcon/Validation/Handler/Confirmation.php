<?php
namespace Phalcon\Validation\Handler
{

    /**
     * confirm the value of current property is equal to another property.
     *
     * @author hu2008yinxiang@163.com
     *        
     */
    class Confirmation extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        protected $target = null;

        protected $targetName = null;
        
        /*
         * target is required.
         * @see \Phalcon\Validation\AnnotationHandler::defaultParams()
         */
        protected function defaultParams()
        {
            return array(
                /*'target' => null,*/
                /*'targetName' => null,*/
                'message' => '{keyName} not match the {targetName}.'
            );
        }

        public function setParams(array $params = null)
        {
            parent::setParams($params);
            if (! isset($params['target'])) {
                throw new \Exception('The target should be set.');
            }
            $this->targetName = $this->target = $params['target'];
            if (isset($params['targetName'])) {
                $this->targetName = $params['targetName'];
            }
        }

        public function checkValid()
        {
            $value = $this->context->{$this->key};
            $targetValue = $this->context->{$this->target};
            return ($targetValue == $value) ? array(
                'valid' => true
            ) : array(
                'valid' => false,
                'message' => $this->getMessage()
            );
        }

        protected function getPlaceHolders()
        {
            return array_merge(parent::getPlaceHolders(), array(
                '{target}' => $this->target,
                '{targetName}' => $this->targetName
            ));
        }
    }
}