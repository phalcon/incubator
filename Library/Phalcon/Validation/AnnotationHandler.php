<?php
namespace Phalcon\Validation
{

    /**
     * The abstract handler of validation annotation, you can impliment the \Phalcon\Validation\AnnotationHandlerInterface::checkValid()
     * to build a custom handler.
     *
     * @see \Phalcon\Validation\AnnotationHandlerInterface::checkValid()
     * @author hu2008yinxiang@163.com
     *        
     */
    abstract class AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        /**
         * The object that need to be validated.
         *
         * @var object
         */
        protected $context = null;

        /**
         * The params of this handler.
         *
         * @var mixed
         */
        protected $params = null;

        /**
         * Message template, there is some default place holders can be used in the template:
         * {key}-the property name
         * {keyName}-the property's readable name (default is same as key)
         * {value}-the value of the property
         *
         * @var string
         */
        protected $message = '{keyName} is not valid.';

        /**
         * current property's name
         *
         * @var string
         */
        protected $key = null;

        /**
         * current propertiy's readable name
         *
         * @var string
         */
        protected $keyName = null;
        
        /*
         * {@inhericdoc}
         * @see \Phalcon\Validation\AnnotationHandlerInterface::setContext()
         */
        public function setContext($context)
        {
            $this->context = $context;
        }
        
        /*
         * {@inhericdoc}
         * @see \Phalcon\Validation\AnnotationHandlerInterface::setParams()
         */
        public function setParams(array $params = null)
        {
            if (is_null($params)) {
                $params = array();
            }
            $this->params = array_merge($this->defaultParams(), $params);
            if (isset($this->params['key'])) {
                $this->key = $this->keyName = $this->params['key'];
            }
            if (isset($this->params['keyName'])) {
                $this->keyName = $this->params['keyName'];
            }
            if (isset($this->params['message'])) {
                $this->message = $this->params['message'];
            }
        }

        /**
         * resolve an annotation which is the param of this handler
         *
         * @param \Phalcon\Annotations\Annotation $annotation            
         * @return \Phalcon\Validation\AnnotationHandlerInterface
         */
        protected function resolveValidationAnnotation($annotation)
        {
            $namespace = '\\Phalcon\\Validation\\Handler';
            if ($annotation->hasNamedArgument('namespace')) {
                $namespace = $annotation->getNamedArgument('namespace');
            }
            $name = sprintf('%s\\%s', $namespace, $annotation->getName());
            $params = array();
            $params['key'] = $this->key;
            $params['keyName'] = $this->keyName;
            if ($annotation->hasNamedArgument('key')) {
                $params['key'] = $params['keyName'] = $annotation->getNamedArgument('key');
            }
            if ($annotation->hasNamedArgument('keyName')) {
                $params['keyName'] = $annotation->getNamedArgument('keyName');
            }
            // recursive handler ?
            $args = $annotation->getArguments();
            if (is_null($args)) {
                $args = array();
            }
            // Parse some special params
            $exprArguments = $annotation->getExprArguments();
            if (! empty($exprArguments)) {
                $this->context->resolveArguments($exprArguments, $args);
            }
            $params = array_merge($params, $args);
            $handler = new $name();
            $handler->setContext($this->context);
            $handler->setParams($params);
            return $handler;
        }

        /**
         * generate the invalid message
         *
         * @return string
         */
        protected function getMessage()
        {
            $placeHolders = $this->getPlaceHolders();
            return str_replace(array_keys($placeHolders), array_values($placeHolders), $this->message);
        }

        /**
         * The default params which will be merged into the presented params
         *
         * @return array
         */
        protected function defaultParams()
        {
            return array();
        }

        /**
         * Get the place holders (which are used to build a invalid message).
         *
         * @return array
         */
        protected function getPlaceHolders()
        {
            return array(
                '{keyName}' => $this->keyName,
                '{key}' => $this->key,
                '{value}' => $this->context->{$this->key}
            );
        }
    }
}