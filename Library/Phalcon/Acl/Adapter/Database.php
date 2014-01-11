<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2012 Phalcon Team (http://www.phalconphp.com)       |
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
use Phalcon\Acl\AdapterInterface;
use Phalcon\Acl\Exception;
use Phalcon\Acl\Resource;
use Phalcon\Acl;
use Phalcon\Acl\Role;

/**
 * Phalcon\Acl\Adapter\Database
 * Manages ACL lists in memory
 */
class Database extends Adapter implements AdapterInterface
{

    protected $_options;

    /**
     * Phalcon\Acl\Adapter\Database
     *
     * @param array $options
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

        $this->_options = $options;
    }

    /**
     * Adds a role to the ACL list. Second parameter lets to inherit access data from other existing role
     * Example:
     * <code>$acl->addRole(new Phalcon\Acl\Role('administrator'), 'consultor');</code>
     * <code>$acl->addRole('administrator', 'consultor');</code>
     *
     * @param  string $role
     * @param  array  $accessInherits
     * @return boolean
     */
    public function addRole($role, $accessInherits = null)
    {

        if (!is_object($role)) {
            $role = new Role($role);
        }

        $exists = $this->_options['db']->fetchOne('SELECT COUNT(*) FROM ' . $this->_options['roles'] . " WHERE name = ?", null, array($role->getName()));
        if (!$exists[0]) {
            $this->_options['db']->execute('INSERT INTO ' . $this->_options['roles'] . " VALUES (?, ?)", array($role->getName(), $role->getDescription()));
            $this->_options['db']->execute('INSERT INTO ' . $this->_options['accessList'] . " VALUES (?, ?, ?, ?)", array($role->getName(), '*', '*', $this->_defaultAccess));
        }

        if ($accessInherits) {
            return $this->addInherit($role->getName(), $accessInherits);
        }

        return true;
    }

    /**
     * Do a role inherit from another existing role
     *
     * @param string $roleName
     * @param string $roleToInherit
     */
    public function addInherit($roleName, $roleToInherit)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->_options['roles'] . " WHERE name = ?";
        $exists = $this->_options['db']->fetchOne($sql, null, array($roleToInherit));
        if (!$exists[0]) {
            throw new Exception("Role '" . $roleToInherit . "' does not exist in the role list");
        }

        $sql = 'SELECT COUNT(*) FROM ' . $this->_options['rolesInherits'] . " WHERE roles_name = ? AND roles_inherit = ?";
        $exists = $this->_options['db']->fetchOne($sql, null, array($roleName, $roleToInherit));
        if (!$exists[0]) {
            $this->_options['db']->execute('INSERT INTO ' . $this->_options['rolesInherits'] . " VALUES (?, ?)", array($roleName, $roleToInherit));
        }
    }

    /**
     * Check whether role exist in the roles list
     *
     * @param  string $roleName
     * @return boolean
     */
    public function isRole($roleName)
    {
        $exists = $this->_options['db']->fetchOne('SELECT COUNT(*) FROM ' . $this->_options['roles'] . " WHERE name = ?", null, array($roleName));
        return (bool) $exists[0];
    }

    /**
     * Check whether resource exist in the resources list
     *
     * @param  string $resourceName
     * @return boolean
     */
    public function isResource($resourceName)
    {
        $exists = $this->_options['db']->fetchOne('SELECT COUNT(*) FROM ' . $this->_options['resources'] . " WHERE name = ?", null, array($resourceName));
        return (bool) $exists[0];
    }

    /**
     * Adds a resource to the ACL list
     * Access names can be a particular action, by example
     * search, update, delete, etc or a list of them
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
     * @param   Phalcon\Acl\Resource $resource
     * @return  boolean
     */
    public function addResource($resource, $accessList = null)
    {

        if (!is_object($resource)) {
            $resource = new Resource($resource);
        }

        $exists = $this->_options['db']->fetchOne('SELECT COUNT(*) FROM ' . $this->_options['resources'] . " WHERE name = ?", null, array($resource->getName()));
        if (!$exists[0]) {
            $this->_options['db']->execute('INSERT INTO ' . $this->_options['resources'] . " VALUES (?, ?)", array($resource->getName(), $resource->getDescription()));
        }

        if ($accessList) {
            return $this->addResourceAccess($resource->getName(), $accessList);
        }

        return true;
    }

    /**
     * Adds access to resources
     *
     * @param string $resourceName
     * @param mixed  $accessList
     */
    public function addResourceAccess($resourceName, $accessList)
    {

        if (!$this->isResource($resourceName)) {
            throw new Exception("Resource '" . $resourceName . "' does not exist in ACL");
        }

        $sql = 'SELECT COUNT(*) FROM ' . $this->_options['resourcesAccesses'] . " WHERE resources_name = ? AND access_name = ?";
        if (is_array($accessList)) {
            foreach ($accessList as $accessName) {
                $exists = $this->_options['db']->fetchOne($sql, null, array($resourceName, $accessName));
                if (!$exists[0]) {
                    $this->_options['db']->execute('INSERT INTO ' . $this->_options['resourcesAccesses'] . " VALUES (?, ?)", array($resourceName, $accessName));
                }
            }
        } else {
            $exists = $this->_options['db']->fetchOne($sql, null, array($resourceName, $accessList));
            if (!$exists[0]) {
                $this->_options['db']->execute('INSERT INTO ' . $this->_options['resourcesAccesses'] . " VALUES (?, ?)", array($resourceName, $accessList));
            }
        }
        return true;
    }

    /**
     * Returns all resources in the access list
     *
     * @return Phalcon\Acl\Resource[]
     */
    public function getResources()
    {
        $resources = array();
        $sql = 'SELECT * FROM ' . $this->_options['resources'];
        foreach ($this->_options['db']->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC) as $row) {
            $resources[] = new Resource($row['name'], $row['description']);
        }
        return $resources;
    }

    /**
     * Returns all resources in the access list
     *
     * @return Phalcon\Acl\Role[]
     */
    public function getRoles()
    {
        $roles = array();
        $sql = 'SELECT * FROM ' . $this->_options['roles'];
        foreach ($this->_options['db']->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC) as $row) {
            $roles[] = new Role($row['name'], $row['description']);
        }
        return $roles;
    }

    /**
     * Removes an access from a resource
     *
     * @param string $resourceName
     * @param mixed  $accessList
     */
    public function dropResourceAccess($resourceName, $accessList)
    {

    }

    /**
     * Inserts/Updates a permission in the access list
     *
     * @param string $roleName
     * @param string $resourceName
     * @param string $access
     * @param int    $access
     * @return boolean
     */
    protected function _insertOrUpdateAccess($roleName, $resourceName, $accessName, $action)
    {

        /**
         * Check if the access is valid in the resource
         */
        $sql = 'SELECT COUNT(*) FROM ' . $this->_options['resourcesAccesses'] . " WHERE resources_name = ? AND access_name = ?";
        $exists = $this->_options['db']->fetchOne($sql, null, array($resourceName, $accessName));
        if (!$exists[0]) {
            throw new Exception("Access '" . $accessName . "' does not exist in resource '" . $resourceName . "' in ACL");
        }

        /**
         * Update the access in access_list
         */
        $sql = 'SELECT COUNT(*) FROM ' . $this->_options['accessList'] . " WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
        $exists = $this->_options['db']->fetchOne($sql, null, array($roleName, $resourceName, $accessName));
        if (!$exists[0]) {
            $sql = 'INSERT INTO ' . $this->_options['accessList'] . ' VALUES (?, ?, ?, ?)';
            $params = array($roleName, $resourceName, $accessName, $action);
        } else {
            $sql = 'UPDATE ' . $this->_options['accessList'] . ' SET allowed = ? WHERE roles_name = ? AND resources_name = ? AND access_name = ?';
            $params = array($action, $roleName, $resourceName, $accessName);
        }

        $this->_options['db']->execute($sql, $params);

        /**
         * Update the access '*' in access_list
         */
        $sql = 'SELECT COUNT(*) FROM ' . $this->_options['accessList'] . " WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
        $exists = $this->_options['db']->fetchOne($sql, null, array($roleName, $resourceName, '*'));
        if (!$exists[0]) {
            $sql = 'INSERT INTO ' . $this->_options['accessList'] . ' VALUES (?, ?, ?, ?)';
            $this->_options['db']->execute($sql, array($roleName, $resourceName, '*', $this->_defaultAccess));
        }

        return true;
    }

    /**
     * Inserts/Updates a permission in the access list
     *
     * @param string $roleName
     * @param string $resourceName
     * @param string $access
     * @param int    $access
     * @return boolean
     */
    protected function _allowOrDeny($roleName, $resourceName, $access, $action)
    {

        if (!$this->isRole($roleName)) {
            throw new Exception('Role "' . $roleName . '" does not exist in the list');
        }

        if (is_array($access)) {
            foreach ($access as $accessName) {
                $this->_insertOrUpdateAccess($roleName, $resourceName, $accessName, $action);
            }
        } else {
            $this->_insertOrUpdateAccess($roleName, $resourceName, $access, $action);
        }
    }

    /**
     * Allow access to a role on a resource
     * You can use '*' as wildcard
     * Ej:
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
        return $this->_allowOrDeny($roleName, $resourceName, $access, Acl::ALLOW);
    }

    /**
     * Deny access to a role on a resource
     * You can use '*' as wildcard
     * Ej:
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
     * @param string $roleName
     * @param string $resourceName
     * @param mixed  $access
     * @return boolean
     */
    public function deny($roleName, $resourceName, $access)
    {
        return $this->_allowOrDeny($roleName, $resourceName, $access, Acl::DENY);
    }

    /**
     * Check whether a role is allowed to access an action from a resource
     * <code>
     * //Does Andres have access to the customers resource to create?
     * $acl->isAllowed('Andres', 'Products', 'create');
     * //Do guests have access to any resource to edit?
     * $acl->isAllowed('guests', '*', 'edit');
     * </code>
     *
     * @param  string $role
     * @param  string $resource
     * @param  mixed  $accessList
     * @return boolean
     */
    public function isAllowed($role, $resource, $access)
    {

        /**
         * Check if there is a specific rule for that resource/access
         */
        $sql = 'SELECT allowed FROM ' . $this->_options['accessList'] . " WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
        $allowed = $this->_options['db']->fetchOne($sql, \Phalcon\Db::FETCH_NUM, array($role, $resource, $access));
        if (is_array($allowed)) {
            return (int) $allowed[0];
        }

        /**
         * Check if there is an common rule for that resource
         */
        $sql = 'SELECT allowed FROM ' . $this->_options['accessList'] . " WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
        $allowed = $this->_options['db']->fetchOne($sql, \Phalcon\Db::FETCH_NUM, array($role, $resource, '*'));
        if (is_array($allowed)) {
            return (int) $allowed[0];
        }

        $sql = 'SELECT roles_inherit FROM ' . $this->_options['rolesInherits'] . ' WHERE roles_name = ?';
        $inheritedRoles = $this->_options['db']->fetchAll($sql, \Phalcon\Db::FETCH_NUM, array($role));

        /**
         * Check inherited roles for a specific rule
         */
        foreach ($inheritedRoles as $row) {
            $sql = 'SELECT allowed FROM ' . $this->_options['accessList'] . " WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
            $allowed = $this->_options['db']->fetchOne($sql, \Phalcon\Db::FETCH_NUM, array($row[0], $resource, $access));
            if (is_array($allowed)) {
                return (int) $allowed[0];
            }
        }

        /**
         * Check inherited roles for a specific rule
         */
        foreach ($inheritedRoles as $row) {
            $sql = 'SELECT allowed FROM ' . $this->_options['accessList'] . " WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
            $allowed = $this->_options['db']->fetchOne($sql, \Phalcon\Db::FETCH_NUM, array($row[0], $resource, '*'));
            if (is_array($allowed)) {
                return (int) $allowed[0];
            }
        }

        /**
         * Check if there is a common rule for that access
         */
        $sql = 'SELECT allowed FROM ' . $this->_options['accessList'] . " WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
        $allowed = $this->_options['db']->fetchOne($sql, \Phalcon\Db::FETCH_NUM, array($role, '*', $access));
        if (is_array($allowed)) {
            return (int) $allowed[0];
        }

        /**
         * Return the default access action
         */
        return $this->_defaultAccess;
    }

}
