<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2015 Phalcon Team (http://www.phalconphp.com)       |
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

use Phalcon\Acl\Adapter;
use Phalcon\Acl\Exception;
use Phalcon\Acl\Resource;
use Phalcon\Acl;
use Phalcon\Acl\Role;

/**
 * Phalcon\Acl\Adapter\Mongo
 * Manages ACL lists using Mongo Collections
 */
class Mongo extends Adapter
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Class constructor.
     *
     * @param  array                  $options
     * @throws \Phalcon\Acl\Exception
     */
    public function __construct($options)
    {
        if (!is_array($options)) {
            throw new Exception("Acl options must be an array");
        }

        if (!isset($options['db'])) {
            throw new Exception("Parameter 'db' is required");
        }

        if (!isset($options['roles'])) {
            throw new Exception("Parameter 'roles' is required");
        }

        if (!isset($options['resources'])) {
            throw new Exception("Parameter 'resources' is required");
        }

        if (!isset($options['resourcesAccesses'])) {
            throw new Exception("Parameter 'resourcesAccesses' is required");
        }

        if (!isset($options['accessList'])) {
            throw new Exception("Parameter 'accessList' is required");
        }

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     * Example:
     * <code>$acl->addRole(new Phalcon\Acl\Role('administrator'), 'consultor');</code>
     * <code>$acl->addRole('administrator', 'consultor');</code>
     *
     * @param  string  $role
     * @param  array   $accessInherits
     * @return boolean
     */
    public function addRole($role, $accessInherits = null)
    {
        if (!is_object($role)) {
            $role = new Role($role);
        }

        $roles = $this->getCollection('roles');
        $exists = $roles->count(array('name' => $role->getName()));

        if (!$exists) {
            $roles->insert(array(
                'name'        => $role->getName(),
                'description' => $role->getDescription()
            ));

            $this->getCollection('accessList')->insert(array(
                'roles_name'     => $role->getName(),
                'resources_name' => '*',
                'access_name'    => '*',
                'allowed'        => $this->_defaultAccess
            ));
        }

        if ($accessInherits) {
            return $this->addInherit($role->getName(), $accessInherits);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string                 $roleName
     * @param  string                 $roleToInherit
     * @throws \Phalcon\Acl\Exception
     */
    public function addInherit($roleName, $roleToInherit)
    {
        // TODO: implement later
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $roleName
     * @return boolean
     */
    public function isRole($roleName)
    {
        return $this->getCollection('roles')->count(array('name' => $roleName)) > 0;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $resourceName
     * @return boolean
     */
    public function isResource($resourceName)
    {
        return $this->getCollection('resources')->count(array('name' => $resourceName)) > 0;
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
     * @param  \Phalcon\Acl\Resource $resource
     * @param  array|string          $accessList
     * @return boolean
     */
    public function addResource($resource, $accessList = null)
    {
        if (!is_object($resource)) {
            $resource = new Resource($resource);
        }

        $resources = $this->getCollection('resources');

        $exists = $resources->count(array('name' => $resource->getName()));
        if (!$exists) {
            $resources->insert(array(
                'name'        => $resource->getName(),
                'description' => $resource->getDescription()
            ));
        }

        if ($accessList) {
            return $this->addResourceAccess($resource->getName(), $accessList);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string                 $resourceName
     * @param  array|string           $accessList
     * @return boolean
     * @throws \Phalcon\Acl\Exception
     */
    public function addResourceAccess($resourceName, $accessList)
    {
        if (!$this->isResource($resourceName)) {
            throw new Exception("Resource '" . $resourceName . "' does not exist in ACL");
        }

        $resourcesAccesses = $this->getCollection('resourcesAccesses');

        if (!is_array($accessList)) {
            $accessList = array($accessList);
        }

        foreach ($accessList as $accessName) {
            $exists = $resourcesAccesses->count(array(
                'resources_name' => $resourceName,
                'access_name'    => $accessName
            ));
            if (!$exists) {
                $resourcesAccesses->insert(array(
                    'resources_name' => $resourceName,
                    'access_name'    => $accessName
                ));
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Acl\Resource[]
     */
    public function getResources()
    {
        $resources = array();

        foreach ($this->getCollection('resources')->find() as $row) {
            $resources[] = new Resource($row['name'], $row['description']);
        }

        return $resources;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Acl\Role[]
     */
    public function getRoles()
    {
        $roles = array();

        foreach ($this->getCollection('roles')->find() as $row) {
            $roles[] = new Role($row['name'], $row['description']);
        }

        return $roles;
    }

    /**
     * {@inheritdoc}
     *
     * @param string       $resourceName
     * @param array|string $accessList
     */
    public function dropResourceAccess($resourceName, $accessList)
    {
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
     * @param string $roleName
     * @param string $resourceName
     * @param mixed  $access
     */
    public function allow($roleName, $resourceName, $access)
    {
        $this->allowOrDeny($roleName, $resourceName, $access, Acl::ALLOW);
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
     * @param  string  $roleName
     * @param  string  $resourceName
     * @param  mixed   $access
     * @return boolean
     */
    public function deny($roleName, $resourceName, $access)
    {
        $this->allowOrDeny($roleName, $resourceName, $access, Acl::DENY);
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
     * @param  string  $role
     * @param  string  $resource
     * @param  string  $access
     * @return boolean
     */
    public function isAllowed($role, $resource, $access)
    {
        $accessList = $this->getCollection('accessList');
        $access     = $accessList->findOne(array(
            'roles_name'     => $role,
            'resources_name' => $resource,
            'access_name'    => $access
        ));

        if (is_array($access)) {
            return (bool) $access['allowed'];
        }

        /**
         * Check if there is an common rule for that resource
         */
        $access = $accessList->findOne(array(
            'roles_name'     => $role,
            'resources_name' => $resource,
            'access_name'    => '*'
        ));
        if (is_array($access)) {
            return (bool) $access['allowed'];
        }

        return $this->_defaultAccess;
    }

    /**
     * Returns a mongo collection
     *
     * @param  string           $name
     * @return \MongoCollection
     */
    protected function getCollection($name)
    {
        return $this->options['db']->selectCollection($this->options[$name]);
    }

    /**
     * Inserts/Updates a permission in the access list
     *
     * @param  string                 $roleName
     * @param  string                 $resourceName
     * @param  string                 $accessName
     * @param  integer                $action
     * @return boolean
     * @throws \Phalcon\Acl\Exception
     */
    protected function insertOrUpdateAccess($roleName, $resourceName, $accessName, $action)
    {
        /**
         * Check if the access is valid in the resource
         */
        $exists = $this->getCollection('resourcesAccesses')->count(array(
            'resources_name' => $resourceName,
            'access_name'    => $accessName
        ));
        if (!$exists) {
            throw new Exception(
                "Access '" . $accessName . "' does not exist in resource '" . $resourceName . "' in ACL"
            );
        }

        $accessList = $this->getCollection('accessList');

        $access = $accessList->findOne(array(
            'roles_name'     => $roleName,
            'resources_name' => $resourceName,
            'access_name'    => $accessName
        ));
        if (!$access) {
            $accessList->insert(array(
                'roles_name'     => $roleName,
                'resources_name' => $resourceName,
                'access_name'    => $accessName,
                'allowed'        => $action
            ));
        } else {
            $access['allowed'] = $action;
            $accessList->save($access);
        }

        /**
         * Update the access '*' in access_list
         */
        $exists = $accessList->count(array(
            'roles_name'     => $roleName,
            'resources_name' => $resourceName,
            'access_name'    => '*'
        ));
        if (!$exists) {
            $accessList->insert(array(
                'roles_name'     => $roleName,
                'resources_name' => $resourceName,
                'access_name'    => '*',
                'allowed'        => $this->_defaultAccess
            ));
        }

        return true;
    }

    /**
     * Inserts/Updates a permission in the access list
     *
     * @param  string                 $roleName
     * @param  string                 $resourceName
     * @param  string                 $access
     * @param  integer                $action
     * @throws \Phalcon\Acl\Exception
     */
    protected function allowOrDeny($roleName, $resourceName, $access, $action)
    {
        if (!$this->isRole($roleName)) {
            throw new Exception('Role "' . $roleName . '" does not exist in the list');
        }

        if (!is_array($access)) {
            $access = array($access);
        }

        foreach ($access as $accessName) {
            $this->insertOrUpdateAccess($roleName, $resourceName, $accessName, $action);
        }
    }
}
