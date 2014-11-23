<?php
namespace Phalcon\Validation
{

    /**
     * The aim of this interface is to create a annotation-based validation
     *
     * @author hu2008yinxiang@163.com
     *        
     */
    interface AnnotationHandlerInterface
    {

        /**
         * Set the context of this handler.
         * you do not need to call it in most situations, it will be called automaticly.
         *
         * @param object $context            
         */
        function setContext($context);

        /**
         * Set params to this handler.
         * you do not need to call it in most situations, it will be called automaticly.
         * <code>
         * $handler = new \Phalcon\Validation\Handler\Regex();
         * $handler->setParams(array('/^[a-zA-Z0-9_]{6,}$/','keyName'='Username'))
         * </code>
         *
         * @param mixed $params            
         */
        function setParams(array $params = null);

        /**
         * Do the check logic.
         * you do not need to call it in most situations, it will be called automaticly.
         * <code>
         * return array('valid'=>true);
         * return array('valid'=>false,'message'=>'the %s is not valid.');
         * </code>
         *
         * @return mixed
         */
        function checkValid();
    }
}