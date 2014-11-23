<?php
namespace Phalcon\Validation
{

    /**
     * Entity can be a POJO class.
     * sub-class of it can be used to do validation.
     * <code>
     * class Pojo extends \Phalcon\Validation\Entity{
     * //@Email(keyName='Your email',message='{keyName} is {value} which is not
     * a VALID email address.')
     * public email = null;
     * //@StringLength(trim=true,min=6,message='{keyName} should have at least
     * {min} characters.')
     * public password = null;
     * }
     *
     * $pojo = new Pojo($di);
     * $pojo->setProperties(array('email'=>'invalid@$$','password'=>' '))
     * var_export($pojo->doValidate());
     * <code>
     * Or you can do it by use the \Phalcon\Validation\EntityAgent class, it can be lower cost (which is not require you to make you POJO class extern the Entity, so you can remove the annotation-based validation with no harm to you code.)
     *
     * @see \Phalcon\Validation\EntityAgent
     * @author hu2008yinxiang@163.com
     *        
     */
    abstract class Entity
    {

        /**
         * These constans are 'copy' from the C source of Phalcon
         *
         * @link https://github.com/phalcon/cphalcon/blob/master/ext/annotations/scanner.h
         */
        const PHANNOT_T_IGNORE = 297;

        const PHANNOT_T_DOCBLOCK_ANNOTATION = 299;

        const PHANNOT_T_ANNOTATION = 300;

        const PHANNOT_T_INTEGER = 301;

        const PHANNOT_T_DOUBLE = 302;

        const PHANNOT_T_STRING = 303;

        const PHANNOT_T_NULL = 304;

        const PHANNOT_T_FALSE = 305;

        const PHANNOT_T_TRUE = 306;

        const PHANNOT_T_IDENTIFIER = 307;

        const PHANNOT_T_ARRAY = 308;

        const PHANNOT_T_ARBITRARY_TEXT = 309;

        /**
         *
         * @var \Phalcon\DiInterface
         */
        protected $di;

        /**
         *
         * @var \Phalcon\Annotations\Annotation[]
         */
        protected static $annotations = null;

        /**
         *
         * @param \Phalcon\DiInterface $di
         *            The dependency injector, need for get the annotation service.
         */
        public function __construct($di)
        {
            $this->di = $di;
            $this->initAnnotations();
        }

        /**
         * Read the annotations of all properties.
         */
        protected function initAnnotations()
        {
            if (static::$annotations) {
                return;
            }
            $annotationService = $this->di->get('annotations');
            static::$annotations = array();
            $annotationArray = $annotationService->getProperties(get_called_class());
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

        /**
         * fill entity with an array
         *
         * @param array $data
         *            value map of the entity
         */
        public function setProperties(array $data = null)
        {
            // TODO do some filter?
            foreach ($data as $k => $v) {
                $this->{$k} = $v;
            }
        }

        /**
         * do the validate action.
         * the return value is like:
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
         * the valid field indcate whether the entity passed the validation.
         * and if didn't, the message field will collect all validation message of invalid field.
         *
         * @return mixed
         */
        public function doValidate()
        {
            $ret = array(
                'valid' => true,
                'message' => array()
            );
            foreach (static::$annotations as $property => $annotations) {
                $messages = array();
                $valid = true;
                foreach ($annotations as $annotation) {
                    $namespace = '\\Phalcon\\Validation\\Handler';
                    if ($annotation->hasNamedArgument('namespace')) {
                        $namespace = $annotation->getNamedArgument('namespace');
                    }
                    $name = sprintf('%s\\%s', $namespace, $annotation->getName());
                    $params = $annotation->getArguments();
                    if (! $annotation->hasNamedArgument('key')) {
                        $params['key'] = $property;
                    }
                    if (! $annotation->hasNamedArgument('keyName')) {
                        $params['keyName'] = $params['key'];
                    }
                    $exprArguments = $annotation->getExprArguments();
                    // var_export($annotation);
                    if (! empty($exprArguments)) {
                        // Resolve special token like constants
                        $this->resolveArguments($exprArguments, $params);
                    }
                    // var_export($params);
                    $handler = new $name();
                    $handler->setContext($this);
                    $handler->setParams($params);
                    $result = $handler->checkValid();
                    if ($result['valid']) {
                        continue;
                    }
                    $valid = false;
                    $messages[] = $result['message'];
                }
                if ($valid) {
                    continue;
                }
                $ret['valid'] = false;
                $ret['message'][$property] = $messages;
            }
            
            return $ret;
        }

        /**
         * Resolve the constants in the params into output
         *
         * @param array $exprArguments            
         * @param mixd $output            
         */
        public function resolveArguments($exprArguments, &$output)
        {
            $curIndex = 0;
            foreach ($exprArguments as $expr) {
                $isNamed = isset($expr['name']);
                $index = $isNamed ? $expr['name'] : $curIndex;
                switch ($expr['expr']['type']) {
                    case static::PHANNOT_T_IDENTIFIER:
                        
                        // Get the constant value
                        $output[$index] = constant($expr['expr']['value']);
                        break;
                    case static::PHANNOT_T_ARRAY:
                        
                        // Recursive resolve
                        $this->resolveArguments($expr['expr']['items'], $output[$index]);
                        break;
                    case static::PHANNOT_T_ANNOTATION:
                        
                        // the annotation current is handled by Phalcon
                        break;
                }
                // Increase the numbered index
                if (! $isNamed) {
                    ++ $curIndex;
                }
            }
        }
    }
}