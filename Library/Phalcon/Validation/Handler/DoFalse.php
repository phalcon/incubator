<?php
namespace Phalcon\Validation\Handler
{

    /**
     * Always fail validation.
     * Can be used in validation chain.
     *
     * @author hu2008yinxiang@163.com
     *        
     */
    class DoFalse extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        public function checkValid()
        {
            return array(
                'valid' => false,
                'message' => $this->getMessage()
            );
        }
    }
}