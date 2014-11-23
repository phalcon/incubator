<?php
namespace Phalcon\Validation
{

    /**
     * The EntityAgent provide annotaion-based validation with no dependency to
     * your exist codes.
     * It is useful if you have already build your POJO class or somehow you
     * can't make your POJO
     * class as a sub-class of the \Phalcon\Validation\Entity
     * <code>
     * class Pojo {
     * //@Email(keyName='Your email',message='{keyName} is {value} which is not
     * a VALID email address.')
     * public email = null;
     * //@StringLength(trim=true,min=6,message='{keyName} should have at least
     * {min} characters.')
     * public password = null;
     * }
     *
     * $pojo = new Pojo();
     * $agent = new Agent($di,$pojo);
     * $agent->setProperties(array('email'=>'invalid@$$','password'=>' '))
     * var_export($agent->doValidate());
     * </code>
     * The output should like:
     * <pre>
     * array(
     * 'valid'=>false,
     * 'message'=>array(
     * 'email'=>array(
     * 0=>'Your email is invalid@$$ which is not a valid email address.'
     * ),
     * 'password'=>array(
     * 0=>'password should have at least 6 characters.'
     * )
     * )
     * )
     * </pre>
     *
     * @author hu2008yinxiang@163.com
     *        
     */
    final class EntityAgent extends Entity
    {

        /**
         * The object which need to be validated.
         *
         * @var object
         */
        protected $target = null;

        /**
         *
         * @param \Phalcon\DiInterface $di
         *            The dependency injector, need for get the annotation service.
         * @param object $target
         *            The object which need to be validate
         */
        public function __construct($di, $target)
        {
            $this->target = $target;
            parent::__construct($di);
        }
        
        /*
         * @see \Phalcon\Validation\Entity::initAnnotations()
         */
        protected function initAnnotations()
        {
            if (static::$annotations) {
                return;
            }
            $annotationService = $this->di->get('annotations');
            static::$annotations = array();
            $annotationArray = $annotationService->getProperties(get_class($this->target));
            $properties = array_keys($annotationArray);
            foreach ($properties as $property) {
                // build a map
                static::$annotations[$property] = array();
            }
            foreach ($annotationArray as $property => $annotationCollection) {
                // scan all annotations
                foreach ($annotationCollection as $annotation) {
                    $namespace = '\\Phalcon\\Validation\\Handler';
                    if ($annotation->hasNamedArgument('namespace')) {
                        $namespace = $annotation->getNamedArgument('namespace');
                    }
                    $name = sprintf('%s\\%s', $namespace, $annotation->getName());
                    // var_export($annotation->getNamedArgument('key','---'));
                    // check if the Annotation is a validation
                    if (is_a($name, '\Phalcon\Validation\AnnotationHandlerInterface', true)) {
                        static::$annotations[$property][] = $annotation;
                    }
                }
            }
        }

        public function __get($name)
        {
            return $this->target->{$name};
        }

        public function __set($name, $value)
        {
            $this->target->{$name} = $value;
        }

        public function __isset($name)
        {
            return isset($this->target->{$name});
        }

        public function __unset($name)
        {
            unset($this->target->{$name});
        }

        public function __call($method, $arguments)
        {
            return call_user_func_array(array(
                $this->target,
                $method
            ), $arguments);
        }
    }
}