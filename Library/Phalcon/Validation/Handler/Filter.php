<?php
namespace Phalcon\Validation\Handler
{

    /**
     * Use the <function>filter_var</function> method as a validate.
     * <code>
     * //@Filter(filter=FILTER_VALIDATE_INT,flag={FILTER_FLAG_ALLOW_HEX,FILTER_FLAG_ALLOW_OCTAL},options={min-range=10,max-range=20})
     * public $amount = 0;
     * </code>
     *
     * @link http://www.php.net/manual/en/filter.constants.php http://www.php.net/manual/en/function.filter-var.php
     * @author hu2008yinxiang@163.com
     *        
     */
    class Filter extends \Phalcon\Validation\AnnotationHandler implements \Phalcon\Validation\AnnotationHandlerInterface
    {

        protected function defaultParams()
        {
            return array_merge(parent::defaultParams(), array(
                'filter' => FILTER_DEFAULT,
                'flag' => FILTER_FLAG_NONE,
                'options' => array()
            ));
        }

        public function checkValid()
        {
            $value = $this->context->{$this->key};
            $flag = FILTER_FLAG_NONE;
            if (is_array($this->params['flag'])) {
                foreach ($this->params['flag'] as $f) {
                    $flag |= $f;
                }
            }
            $ret = filter_var($value, $this->params['filter'], $flag, $options);
            return $ret === false ? array(
                'valid' => false,
                'message' => $this->getMessage()
            ) : array(
                'valid' => true
            );
        }
    }
}