<?php
namespace Phalcon\Validation\Handler
{

    /**
     * Always success validation.
     * Can be used in validation chain.
     * @author 继续
     *
     */
    class DoTrue extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        public function checkValid()
        {
            return array(
                'valid' => true
            );
        }
    }
}