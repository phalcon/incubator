<?php namespace Phalcon\Mvc\Model;

use Phalcon\Mvc\Model\EagerLoading\Loader;

trait EagerLoadingTrait
{

    /**
     * <code>
     * <?php
     *
     * $limit  = 100;
     * $offset = max(0, $this->request->getQuery('page', 'int') - 1) * $limit;
     *
     * $manufacturers = Manufacturer::with('Robots.Parts', [
     *     'limit' => [$limit, $offset]
     * ]);
     *
     * foreach ($manufacturers as $manufacturer) {
     *     foreach ($manufacturer->robots as $robot) {
     *         foreach ($robot->parts as $part) { ... }
     *     }
     * }
     *
     * </code>
     *
     * @param mixed ...$arguments
     * @return \Phalcon\Mvc\ModelInterface[]
     */
    public static function with()
    {
        $arguments = func_get_args();

        if (!empty($arguments)) {
            $numArgs    = count($arguments);
            $lastArg    = $numArgs - 1;
            $parameters = null;

            if ($numArgs >= 2 && is_array($arguments[$lastArg])) {
                $parameters = $arguments[$lastArg];

                unset($arguments[$lastArg]);

                if (isset($parameters['columns'])) {
                    throw new \LogicException('Results from database must be full models, do not use `columns` key');
                }
            }
        } else {
            throw new \BadMethodCallException(sprintf('%s requires at least one argument', __METHOD__));
        }

        $ret = static::find($parameters);

        if ($ret[0]) {
            array_unshift($arguments, $ret);

            $ret = call_user_func_array('Phalcon\Mvc\Model\EagerLoading\Loader::fromResultset', $arguments);
        }

        return $ret;
    }

    /**
     * Same as EagerLoadingTrait::with() for a single record
     *
     * @param mixed ...$arguments
     * @return false|\Phalcon\Mvc\ModelInterface
     */
    public static function findFirstWith()
    {
        $arguments = func_get_args();

        if (!empty($arguments)) {
            $numArgs    = count($arguments);
            $lastArg    = $numArgs - 1;
            $parameters = null;

            if ($numArgs >= 2 && is_array($arguments[$lastArg])) {
                $parameters = $arguments[$lastArg];

                unset($arguments[$lastArg]);

                if (isset($parameters['columns'])) {
                    throw new \LogicException('Results from database must be full models, do not use `columns` key');
                }
            }
        } else {
            throw new \BadMethodCallException(sprintf('%s requires at least one argument', __METHOD__));
        }

        if ($ret = static::findFirst($parameters)) {
            array_unshift($arguments, $ret);

            $ret = call_user_func_array('Phalcon\Mvc\Model\EagerLoading\Loader::fromModel', $arguments);
        }

        return $ret;
    }

    /**
     * <code>
     * <?php
     *
     * $manufacturer = Manufacturer::findFirstById(51);
     *
     * $manufacturer->load('Robots.Parts');
     *
     * foreach ($manufacturer->robots as $robot) {
     *    foreach ($robot->parts as $part) { ... }
     * }
     * </code>
     *
     * @param mixed ...$arguments
     * @return self
     */
    public function load()
    {
        $arguments = func_get_args();

        array_unshift($arguments, $this);

        return call_user_func_array('Phalcon\Mvc\Model\EagerLoading\Loader::fromModel', $arguments);
    }
}
