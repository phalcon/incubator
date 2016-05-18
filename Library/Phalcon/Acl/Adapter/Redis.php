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
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Acl\Adapter;

use Phalcon\Db;
use Phalcon\Acl\Adapter;
use Phalcon\Acl\Exception;
use Phalcon\Acl\Resource;
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\RoleInterface;

/**
 * Phalcon\Acl\Adapter\Database
 * Manages ACL lists in memory
 */
class Redis extends Adapter
{
    /** @var bool  */
    protected $setNXAccess = true;

    /** @var \Redis */
    protected $redis;

    public function __construct($redis = null)
    {
        $this->redis = $redis;
    }

    public function setRedis($redis, $chainRedis = false)
    {
        $this->redis = $redis;
        return $chainRedis ? $redis : $this;
    }

    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * {@inheritdoc}
     *
     * Example:
     * <code>$acl->addRole(new Phalcon\Acl\Role('administrator'), 'consultor');</code>
     * <code>$acl->addRole('administrator', 'consultor');</code>
     *
     * @param  \Phalcon\Acl\Role|string $role
     * @param  string $accessInherits
     * @return boolean
     * @throws \Phalcon\Acl\Exception
     */
    public function addRole($role, $accessInherits = null)
    {
        if (is_string($role)) {
            $role = new Role($role, ucwords($role) . ' Role');
        }

        if (!$role instanceof RoleInterface) {
            throw new Exception('Role must be either an string or implement RoleInterface');
        }

        $this->redis->hMset('roles', [$role->getName() => $role->getDescription()]);
        $this->redis->sAdd("accessList:$role:*:{$this->getDefaultAction()}}", "*");

        if ($accessInherits) {
            $this->addInherit($role->getName(), $accessInherits);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $roleName
     * @param  string $roleToInherit
     * @throws \Phalcon\Acl\Exception
     */
    public function addInherit($roleName, $roleToInherit)
    {
        $exists = $this->redis->hGet('roles', $roleName);

        if (!$exists) {
            throw new Exception(
                sprintf("Role '%s' does not exist in the role list", $roleName)
            );
        }

        $this->redis->sAdd("rolesInherits:$roleName", $roleToInherit);
    }

    /**
     * {@inheritdoc}
     * Example:
     * <code>
     * //Add a resource to the the list allowing access to an action
     * $acl->addResource(new Phalcon\Acl\Resource('customers'), 'search');
     * $acl->addResource('customers', 'search');
     * //Add a resource  with an access list
     * $acl->addResource(new Phalcon\Acl\Resource('customers'), array('create', 'search'));
     * $acl->addResource('customers', array('create', 'search'));
     * </code>
     *
     * @param  \Phalcon\Acl\Resource|string $resource
     * @param  array|string $accessList
     * @return boolean
     */
    public function addResource($resource, $accessList = null)
    {
        if (!is_object($resource)) {
            $resource = new Resource($resource, ucwords($resource) . " Resource");
        }
        $this->redis->hMset("resources", array($resource->getName() => $resource->getDescription()));

        if ($accessList) {
            return $this->addResourceAccess($resource->getName(), $accessList);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $resourceName
     * @param  array|string $accessList
     * @return boolean
     * @throws \Phalcon\Acl\Exception
     */
    public function addResourceAccess($resourceName, $accessList)
    {
        if (!$this->isResource($resourceName)) {
            throw new Exception("Resource '" . $resourceName . "' does not exist in ACL");
        }

        $accessList = (is_string($accessList)) ? explode(' ', $accessList) : $accessList;
        foreach ($accessList as $accessName) {
            $this->redis->sAdd("resourcesAccesses:$resourceName", $accessName);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $roleName
     * @return boolean
     */
    public function isRole($roleName)
    {
        return $this->redis->hExists("roles", $roleName);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $resourceName
     * @return boolean
     */
    public function isResource($resourceName)
    {
        return $this->redis->hExists("resources", $resourceName);
    }

    public function isResourceAccess($resource, $access)
    {
        return $this->redis->sIsMember("resourcesAccesses:$resource", $access);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Acl\Resource[]
     */
    public function getResources()
    {
        $resources = array();

        foreach ($this->redis->hGetAll("resources") as $name => $desc) {
            $resources[] = new Resource($name, $desc);
        }

        return $resources;
    }

    /**
     * {@inheritdoc}
     *
     * @return RoleInterface[]
     */
    public function getRoles()
    {
        $roles = array();

        foreach ($this->redis->hGetAll("roles") as $name => $desc) {
            $roles[] = new Role($name, $desc);
        }

        return $roles;
    }

    /**
     * @param $role
     * @return array
     */
    public function getRoleInherits($role)
    {
        return $this->redis->sMembers("rolesInherits:$role");
    }

    public function getResourceAccess($resource)
    {
        return $this->redis->sMembers("resourcesAccesses:$resource");
    }

    /**
     * {@inheritdoc}
     *
     * @param string $resource
     * @param array|string $accessList
     */
    public function dropResourceAccess($resource, $accessList)
    {
        if (!is_array($accessList)) {
            $accessList = (array)$accessList;
        }
        array_unshift($accessList, "resourcesAccesses:$resource");
        call_user_func_array(array($this->redis, 'sRem'), $accessList);
    }

    /**
     * {@inheritdoc}
     * You can use '*' as wildcard
     * Example:
     * <code>
     * //Allow access to guests to search on customers
     * $acl->allow('guests', 'customers', 'search');
     * //Allow access to guests to search or create on customers
     * $acl->allow('guests', 'customers', array('search', 'create'));
     * //Allow access to any role to browse on products
     * $acl->allow('*', 'products', 'browse');
     * //Allow access to any role to browse on any resource
     * $acl->allow('*', '*', 'browse');
     * </code>
     *
     * @param string $role
     * @param string $resource
     * @param array|string $access
     */
    public function allow($role, $resource, $access)
    {
        if ($role !== '*' && $resource !== '*') {
            $this->allowOrDeny($role, $resource, $access, Acl::ALLOW);
        }
        if ($role === '*' || empty($role)) {
            $this->rolePermission($resource, $access, Acl::ALLOW);
        }
        if ($resource === '*' || empty($resource)) {
            $this->resourcePermission($role, $access, Acl::ALLOW);
        }
    }

    /**
     * @param $role
     * @param $access
     * @param $allowOrDeny
     * @throws Exception
     */
    protected function resourcePermission($role, $access, $allowOrDeny)
    {
        foreach ($this->getResources() as $resource) {
            if ($role === '*' || empty($role)) {
                $this->rolePermission($resource, $access, $allowOrDeny);
            } else {
                $this->allowOrDeny($role, $resource, $access, $allowOrDeny);
            }
        }
    }

    /**
     * @param $resource
     * @param $access
     * @param $allowOrDeny
     * @throws Exception
     */
    protected function rolePermission($resource, $access, $allowOrDeny)
    {
        foreach ($this->getRoles() as $role) {
            if ($resource === '*' || empty($resource)) {
                $this->resourcePermission($role, $access, $allowOrDeny);
            } else {
                $this->allowOrDeny($role, $resource, $access, $allowOrDeny);
            }
        }
    }

    /**
     * {@inheritdoc}
     * You can use '*' as wildcard
     * Example:
     * <code>
     * //Deny access to guests to search on customers
     * $acl->deny('guests', 'customers', 'search');
     * //Deny access to guests to search or create on customers
     * $acl->deny('guests', 'customers', array('search', 'create'));
     * //Deny access to any role to browse on products
     * $acl->deny('*', 'products', 'browse');
     * //Deny access to any role to browse on any resource
     * $acl->deny('*', '*', 'browse');
     * </code>
     *
     * @param  string $roleName
     * @param  string $resourceName
     * @param  array|string $access
     * @return boolean
     */
    public function deny($role, $resource, $access)
    {
        if ($role === '*' || empty($role)) {
            $this->rolePermission($resource, $access, Acl::DENY);
        } elseif ($resource === '*' || empty($resource)) {
            $this->resourcePermission($role, $access, Acl::DENY);
        } else {
            $this->allowOrDeny($role, $resource, $access, Acl::DENY);
        }
    }

    /**
     * {@inheritdoc}
     * Example:
     * <code>
     * //Does Andres have access to the customers resource to create?
     * $acl->isAllowed('Andres', 'Products', 'create');
     * //Do guests have access to any resource to edit?
     * $acl->isAllowed('guests', '*', 'edit');
     * </code>
     *
     * @param string $role
     * @param string $resource
     * @param string $access
     *
     * @return bool
     */
    public function isAllowed($role, $resource, $access)
    {
        if ($this->redis->sIsMember("accessList:$role:$resource:" . Acl::ALLOW, $access)) {
            return Acl::ALLOW;
        }

        if ($this->redis->exists("rolesInherits:$role")) {
            $rolesInherits = $this->redis->sMembers("rolesInherits:$role");
            foreach ($rolesInherits as $role) {
                if ($this->redis->sIsMember("accessList:$role:$resource:" . Acl::ALLOW, $access)) {
                    return Acl::ALLOW;
                }
            }
        }

        /**
         * Return the default access action
         */

        return $this->getDefaultAction();
    }

    /**
     * @param $roleName
     * @param $resourceName
     * @param $accessName
     * @param $action
     * @return bool
     * @throws Exception
     */
    protected function setAccess($roleName, $resourceName, $accessName, $action)
    {
        /**
         * Check if the access is valid in the resource
         */
        if ($this->isResourceAccess($resourceName, $accessName)) {
            if (!$this->setNXAccess) {
                throw new Exception(
                    "Access '" . $accessName . "' does not exist in resource '" . $resourceName . "' in ACL"
                );
            }
            $this->addResourceAccess($resourceName, $accessName);
        }
        $this->redis->sAdd("accessList:$roleName:$resourceName:$action", $accessName);
        $accessList = "accessList:$roleName:$resourceName";

        // remove first if exists
        foreach (array(1, 2) as $act) {
            $this->redis->sRem("$accessList:$act", $accessName);
            $this->redis->sRem("$accessList:$act", "*");
        }

        $this->redis->sAdd("$accessList:$action", $accessName);

        $this->redis->sAdd("$accessList:{$this->getDefaultAction()}", "*");

        return true;
    }

    /**
     * Inserts/Updates a permission in the access list
     *
     * @param  string $roleName
     * @param  string $resourceName
     * @param  array|string $access
     * @param  integer $action
     * @throws \Phalcon\Acl\Exception
     */
    protected function allowOrDeny($roleName, $resourceName, $access, $action)
    {
        if (!$this->isRole($roleName)) {
            throw new Exception('Role "' . $roleName . '" does not exist in the list');
        }
        if (!$this->isResource($resourceName)) {
            throw new Exception('Resource "' . $resourceName . '" does not exist in the list');
        }
        $access = ($access === '*' || empty($access)) ? $this->getResourceAccess($resourceName) : $access;
        if (is_array($access)) {
            foreach ($access as $accessName) {
                $this->setAccess($roleName, $resourceName, $accessName, $action);
            }
        } else {
            $this->setAccess($roleName, $resourceName, $access, $action);
        }
    }
}
