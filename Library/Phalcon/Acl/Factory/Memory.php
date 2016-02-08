<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Nemanja Ognjanovic <ognjanovic@gmail.com>                     |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Acl\Factory;

use Phalcon\Config;
use Phalcon\Acl\Adapter\Memory as MemoryAdapter;
use Phalcon\Acl\Exception;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Role;

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
     * @var Config
     */
    private $config;

    /**
     * @var MemoryAdapter
     */
    private $acl;

    /**
     * Array of defined role objects.
     *
     * @var Role[]
     */
    private $roles = [];

    /**
     * Creates configured instance of acl.
     *
     * @param Config $config config
     * @return MemoryAdapter acl
     * @throws Exception If configuration is wrong
     */
    public function create(Config $config)
    {
        $this->acl = new MemoryAdapter();
        $this->config = $config;
        $defaultAction = $this->config->get('defaultAction');

        if (!is_int($defaultAction) && !ctype_digit($defaultAction)) {
            throw new Exception('Key "defaultAction" must exist and must be of numeric value.');
        }

        $this->acl->setDefaultAction((int) $defaultAction);
        $this->addResources();
        $this->addRoles();

        return $this->acl;
    }

    /**
     * Adds resources from config to acl object.
     *
     * @return $this
     * @throws Exception
     */
    protected function addResources()
    {
        if (!(array)$this->config->get('resource')) {
            throw new Exception('Key "resource" must exist and must be traversable.');
        }

        // resources
        foreach ($this->config->get('resource') as $name => $resource) {
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
     * @throws Exception
     */
    protected function addRoles()
    {
        if (!(array)$this->config->get('role')) {
            throw new Exception('Key "role" must exist and must be traversable.');
        }

        foreach ($this->config->get('role') as $role => $rules) {
            $this->roles[$role] = $this->makeRole($role, $rules->get('description'));
            $this->addRole($role, $rules);
            $this->addAccessRulesToRole($role, $rules);
        }

        return $this;
    }

    /**
     * Adds access rules to role.
     *
     * @param string $role  role
     * @param Config $rules rules
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function addAccessRulesToRole($role, Config $rules)
    {
        foreach ($rules as $method => $rules) {
            // skip not wanted rules
            if (in_array($method, ['inherit', 'description'])) {
                continue;
            }

            foreach ($rules as $controller => $actionRules) {
                $actions = $this->castAction($actionRules->get('actions'));

                if (!in_array($method, ['allow', 'deny'])) {
                    throw new Exception(sprintf(
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
     * Cast actions
     *
     * @param mixed $actions Actions
     * @return array|null
     * @throws Exception
     */
    protected function castAction($actions)
    {
        if ($actions instanceof Config) {
            $actions = $actions->toArray();
        } elseif (is_string($actions)) {
            $actions = [$actions];
        }

        if (!is_array($actions)) {
            throw new Exception(
                'Key "actions" must exist and must be traversable.'
            );
        }

        return $actions;
    }

    /**
     * Add role to acl.
     *
     * @param string $role  role
     * @param Config $rules rules
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function addRole($role, Config $rules)
    {
        // role has inheritance ?
        if ($rules->get('inherit')) {
            // role exists?
            if (!array_key_exists($rules->inherit, $this->roles)) {
                throw new Exception(sprintf(
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
     * @param string      $name        Resource name
     * @param string|null $description Resource description [Optional]
     *
     * @return Resource
     */
    protected function makeResource($name, $description = null)
    {
        return new Resource($name, $description);
    }

    /**
     * Creates acl role.
     *
     * @param string      $role        Role name
     * @param string|null $description Description [Optional]
     *
     * @return Role
     */
    protected function makeRole($role, $description = null)
    {
        return new Role($role, $description);
    }
}
