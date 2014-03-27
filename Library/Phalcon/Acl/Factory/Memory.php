<?php
/**
 * Phalcon Framework
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phalconphp.com so we can send you a copy immediately.
 *
 * @author Nemanja Ognjanovic <ognjanovic@gmail.com>
 */
namespace Phalcon\Acl\Factory;

/**
 * Class Memory
 *
 * This factory is intended to be used to ease setup of \Phalcon\Acl\Adapter\Memory
 * in case \Phalcon\Config is used for configuration.
 *
 * @package Phalcon\Acl\Factory
 */
class Memory
{
    /**
     * @var \Phalcon\Config
     */
    private $config;

    /**
     * @var \Phalcon\Acl\Adapter\Memory
     */
    private $acl;

    /**
     * Array of defined role objects.
     *
     * @var \Phalcon\Acl\Role[]
     */
    private $roles = array();

    /**
     * Creates configured instance of acl.
     *
     * @param \Phalcon\Config $config config
     *
     * @return \Phalcon\Acl\Adapter\Memory acl
     *
     * @throws \Phalcon\Acl\Exception      If configuration is wrong
     */
    public function create(\Phalcon\Config $config)
    {
        $this->acl = new \Phalcon\Acl\Adapter\Memory();
        $this->config = $config;

        if (!is_numeric($this->config->get('defaultAction'))) {
            throw new \Phalcon\Acl\Exception('Key "defaultAction" must exist and must be of numeric value.');
        }
        $this->acl->setDefaultAction((int) $this->config->defaultAction);
        $this->addResources();
        $this->addRoles();
        return $this->acl;
    }

    /**
     * Adds resources from config to acl object.
     *
     * @return $this
     * @throws \Phalcon\Acl\Exception
     */
    protected function addResources()
    {
        if (!(array)$this->config->get('resource')) {
            throw new \Phalcon\Acl\Exception('Key "resource" must exist and must be traversable.');
        }

        // resources
        foreach ($this->config->resource as $name => $resource) {
            $actions = (array) $resource->get('actions');
            if (!$actions) {
                $actions = null;
            }
            $this->acl->addResource(
                $this->makeResource($name, $resource->description),
                $actions
            );
        }

        return $this;
    }

    /**
     * Adds role from config to acl object.
     *
     * @return $this
     * @throws \Phalcon\Acl\Exception
     */
    protected function addRoles()
    {
        if (!(array)$this->config->get('role')) {
            throw new \Phalcon\Acl\Exception('Key "role" must exist and must be traversable.');
        }

        foreach ($this->config->role as $role => $rules) {
            $this->roles[$role] = $this->makeRole($role, $rules->get('description'));
            $this->addRole($role, $rules);
            $this->addAccessRulesToRole($role, $rules);
        }

        return $this;
    }

    /**
     * Adds access rules to role.
     *
     * @param string          $role  role
     * @param \Phalcon\Config $rules rules
     *
     * @return $this
     *
     * @throws \Phalcon\Acl\Exception
     */
    protected function addAccessRulesToRole($role, \Phalcon\Config $rules)
    {
        foreach ($rules as $method => $rules) {
            // skip not wanted rules
            if (in_array($method, array('inherit', 'description'))) {
                continue;
            }

            foreach ($rules as $controller => $actionRules) {
                $actions = (array) $actionRules->get('actions');
                if (!$actions) {
                    throw new \Phalcon\Acl\Exception(
                        'Key "actions" must exist and must be traversable.'
                    );
                }
                if (!in_array($method, array('allow', 'deny'))) {
                    throw new \Phalcon\Acl\Exception(sprintf(
                        'Wrong access method given. Expected "allow" or "deny" but "%s" was set.',
                        $method
                    ));
                }
                $this->acl->{$method}($role, $controller, $actions);
            }
        }

        return $this;
    }

    /**
     * Add role to acl.
     *
     * @param string          $role  role
     * @param \Phalcon\Config $rules rules
     *
     * @return $this
     *
     * @throws \Phalcon\Acl\Exception
     */
    protected function addRole($role, \Phalcon\Config $rules)
    {
        // role has inheritance ?
        if ($rules->get('inherit')) {
            // role exists?
            if (!array_key_exists($rules->inherit, $this->roles)) {
                throw new \Phalcon\Acl\Exception(sprintf(
                    'Role "%s" cannot inherit non-existent role "%s".
                     Either such role does not exist or it is set to be inherited before it is actually defined.',
                    $role,
                    $rules->inherit
                ));
            }
            $this->acl->addRole($this->roles[$role], $this->roles[$rules->inherit]);
        } else {
            $this->acl->addRole($this->roles[$role]);
        }

        return $this;
    }

    /**
     * Creates acl resource.
     *
     * @param string      $name        resource name
     * @param string|null $description optional, resource description
     *
     * @return \Phalcon\Acl\Resource
     */
    protected function makeResource($name, $description = null)
    {
        return new \Phalcon\Acl\Resource(
            $name,
            $description
        );
    }

    /**
     * Creates acl role.
     *
     * @param string      $role        role name
     * @param string|null $description optional, description
     *
     * @return \Phalcon\Acl\Role
     */
    protected function makeRole($role, $description = null)
    {
        return new \Phalcon\Acl\Role($role, $description);
    }
}
