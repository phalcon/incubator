<?php
namespace Phalcon\Validation\Handler
{

    /**
     * Chain some validation annotation with the OR operation.
     * It will stop and success if any sub-validation successed.
     * 
     * @author hu2008yinxiang@163.com
     *        
     */
    class DoOr extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        public function checkValid()
        {
            foreach ($this->params as $param) {
                if ($param instanceof \Phalcon\Annotations\Annotation) {
                    $param = $this->resolveValidationAnnotation($param);
                    $result = $param->checkValid();
                    if ($result['valid']) {
                        return $result;
                    }
                }
            }
            return array(
                'valid' => false,
                'message' => $this->getMessage()
            );
        }
    }
}