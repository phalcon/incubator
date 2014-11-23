<?php
namespace Phalcon\Validation\Handler
{

    /**
     * Reverse the validation result of another validation.
     * 
     * @author hu2008yinxiang@163.com
     *        
     */
    class DoNot extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        public function checkValid()
        {
            $param = $this->params[0]; // the first unnamed param
            if ($param instanceof \Phalcon\Annotations\Annotation) {
                $param = $this->resolveValidationAnnotation($param);
                $result = $param->checkValid();
                return $result['valid'] ? array(
                    'valid' => false,
                    'message' => $this->getMessage()
                ) : array(
                    'valid' => true
                );
            } else {
                // fallback when the param can't be recognized
                return empty($param) ? array(
                    'valid' => true
                ) : array(
                    'valid' => false,
                    'message' => $this->getMessage()
                );
            }
        }
    }
}