<?php
namespace Phalcon\Validation\Handler
{

    /**
     * validate the field is in the specified collect.
     * <code>
     * // Use an array to provide the search source
     * //@InclusionIn({'apple','banana'})
     * public $type=null
     * // Use a method result as a search source
     * //@InclusionIn('supportedTypes')
     * public $featured_language=null;
     * public function supportedTypes(){
     * return array('php','C++','Java');
     * }
     * </code>
     *
     * @author hu2008yinxiang@163.com
     *        
     */
    class InclusionIn extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        protected function defaultParams()
        {
            return array(
                'strict' => false
            );
        }

        public function checkValid()
        {
            $param = $this->params[0];
            if (is_string($param)) {
                $param = $this->context->{$param}();
            }
            $value = $this->context->{$this->key};
            return (! in_array($value, $param, $this->params['strict'])) ? array(
                'valid' => false,
                'message' => $this->getMessage()
            ) : array(
                'valid' => true
            );
        }
    }
}