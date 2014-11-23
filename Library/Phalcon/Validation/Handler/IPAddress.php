<?php
namespace Phalcon\Validation\Handler
{

    /**
     * Equivalent to Filter(filter=FILTER_VALIDATE_EMAIL)
     * 
     * @author hu2008yinxiang@163.com
     *        
     */
    class IPAddress extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        /**
         */
        protected function defaultParams()
        {
            return array_merge(parent::defaultParams(), array(
                'allowIPv6' => true,
                'allowIPv4' => true,
                'noPrivate' => true,
                'noReserved' => true,
                'message' => '{value} is not a valid IP Address'
            ));
        }

        public function checkValid()
        {
            $flag = ($this->params['allowIPv6'] ? FILTER_FLAG_IPV6 : FILTER_FLAG_NONE) | ($this->params['allowIPv4'] ? FILTER_FLAG_IPV4 : FILTER_FLAG_NONE) | ($this->params['noPrivate'] ? FILTER_FLAG_NO_PRIV_RANGE : FILTER_FLAG_NONE) | ($this->params['noReserved'] ? FILTER_FLAG_NO_RES_RANGE : FILTER_FLAG_NONE);
            $value = $this->context->{$this->key};
            return filter_var($value, FILTER_VALIDATE_IP, $flag) === false ? array(
                'valid' => false,
                'message' => $this->getMessage()
            ) : array(
                'valid' => true
            );
        }
    }
}