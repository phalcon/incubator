<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2014 Phalcon Team (http://www.phalconphp.com)       |
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
    public function __construct(array $options)
    {
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
     * @param  \Phalcon\Acl\Role|string $role
     * @param  string                   $accessInherits
     * @return boolean
     */
    public function addRole($role, $accessInherits = null)
    {
        if (!is_object($role)) {
            $role = new Role($role);
        }

        $exists = $this->options['db']->fetchOne(
            'SELECT COUNT(*) FROM ' . $this->options['roles'] . ' WHERE name = ?',
            null,
            array($role->getName())
        );

        if (!$exists[0]) {
            $this->options['db']->execute(
                'INSERT INTO ' . $this->options['roles'] . ' VALUES (?, ?)',
                array($role->getName(), $role->getDescription())
            );

            $this->options['db']->execute(
                'INSERT INTO ' . $this->options['accessList'] . ' VALUES (?, ?, ?, ?)',
                array($role->getName(), '*', '*', $this->_defaultAccess)
            );
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
        $sql = 'SELECT COUNT(*) FROM ' . $this->options['roles'] . ' WHERE name = ?';
        $exists = $this->options['db']->fetchOne($sql, null, array($roleToInherit));
        if (!$exists[0]) {
            throw new Exception("Role '" . $roleToInherit . "' does not exist in the role list");
        }

        $exists = $this->options['db']->fetchOne(
            'SELECT COUNT(*) FROM ' . $this->options['rolesInherits'] . ' WHERE roles_name = ? AND roles_inherit = ?',
            null,
            array($roleName, $roleToInherit)
        );

        if (!$exists[0]) {
            $this->options['db']->execute(
                'INSERT INTO ' . $this->options['rolesInherits'] . ' VALUES (?, ?)',
                array($roleName, $roleToInherit)
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $roleName
     * @return boolean
     */
    public function isRole($roleName)
    {
        $exists = $this->options['db']->fetchOne(
            'SELECT COUNT(*) FROM ' . $this->options['roles'] . ' WHERE name = ?',
            null,
            array($roleName)
        );

        return (bool) $exists[0];
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $resourceName
     * @return boolean
     */
    public function isResource($resourceName)
    {
        $exists = $this->options['db']->fetchOne(
            'SELECT COUNT(*) FROM ' . $this->options['resources'] . ' WHERE name = ?',
            null,
            array($resourceName)
        );

        return (bool) $exists[0];
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
     * @param  array|string                 $accessList
     * @return boolean
     */
    public function addResource($resource, $accessList = null)
    {
        if (!is_object($resource)) {
            $resource = new Resource($resource);
        }

        $exists = $this->options['db']->fetchOne(
            'SELECT COUNT(*) FROM ' . $this->options['resources'] . ' WHERE name = ?',
            null,
            array($resource->getName())
        );

        if (!$exists[0]) {
            $this->options['db']->execute(
                'INSERT INTO ' . $this->options['resources'] . ' VALUES (?, ?)',
                array($resource->getName(), $resource->getDescription())
            );
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

        $sql = 'SELECT COUNT(*) FROM ' .
            $this->options['resourcesAccesses'] .
            ' WHERE resources_name = ? AND access_name = ?';
        if (is_array($accessList)) {
            foreach ($accessList as $accessName) {
                $exists = $this->options['db']->fetchOne($sql, null, array($resourceName, $accessName));
                if (!$exists[0]) {
                    $this->options['db']->execute(
                        'INSERT INTO ' . $this->options['resourcesAccesses'] . ' VALUES (?, ?)',
                        array($resourceName, $accessName)
                    );
                }
            }
        } else {
            $exists = $this->options['db']->fetchOne($sql, null, array($resourceName, $accessList));
            if (!$exists[0]) {
                $this->options['db']->execute(
                    'INSERT INTO ' . $this->options['resourcesAccesses'] . ' VALUES (?, ?)',
                    array($resourceName, $accessList)
                );
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
        $sql       = 'SELECT * FROM ' . $this->options['resources'];

        foreach ($this->options['db']->fetchAll($sql, Db::FETCH_ASSOC) as $row) {
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
        $sql   = 'SELECT * FROM ' . $this->options['roles'];

        foreach ($this->options['db']->fetchAll($sql, Db::FETCH_ASSOC) as $row) {
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
     * @param string       $roleName
     * @param string       $resourceName
     * @param array|string $access
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
     * @param  string       $roleName
     * @param  string       $resourceName
     * @param  array|string $access
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
     * @param string $role
     * @param string $resource
     * @param string $access
     *
     * @return bool
     */
    public function isAllowed($role, $resource, $access)
    {
        /**
         * Check if there is a specific rule for that resource/access
         */
        $sql = 'SELECT allowed FROM ' .
            $this->options['accessList'] .
            ' WHERE roles_name = ? AND resources_name = ? AND access_name = ?';
        $allowed = $this->options['db']->fetchOne($sql, Db::FETCH_NUM, array($role, $resource, $access));
        if (is_array($allowed)) {
            return (bool) $allowed[0];
        }

        /**
         * Check if there is an common rule for that resource
         */
        $sql = 'SELECT allowed FROM ' .
            $this->options['accessList'] .
            ' WHERE roles_name = ? AND resources_name = ? AND access_name = ?';
        $allowed = $this->options['db']->fetchOne($sql, Db::FETCH_NUM, array($role, $resource, '*'));
        if (is_array($allowed)) {
            return (bool) $allowed[0];
        }

        $sql = 'SELECT roles_inherit FROM ' . $this->options['rolesInherits'] . ' WHERE roles_name = ?';
        $inheritedRoles = $this->options['db']->fetchAll($sql, Db::FETCH_NUM, array($role));

        /**
         * Check inherited roles for a specific rule
         */
        $sqlAccess = 'SELECT allowed FROM ' .
            $this->options['accessList'] .
            ' WHERE roles_name = ? AND resources_name = ? AND access_name = ?';

        foreach ($inheritedRoles as $row) {
            $allowed = $this->options['db']->fetchOne($sqlAccess, Db::FETCH_NUM, array($row[0], $resource, $access));
            if (is_array($allowed)) {
                return (bool) $allowed[0];
            }
        }

        /**
         * Check inherited roles for a specific rule
         */
        $sqlAccessAny = 'SELECT allowed FROM ' .
            $this->options['accessList'] .
            ' WHERE roles_name = ? AND resources_name = ? AND access_name = ?';

        foreach ($inheritedRoles as $row) {
            $allowed = $this->options['db']->fetchOne($sqlAccessAny, Db::FETCH_NUM, array($row[0], $resource, '*'));
            if (is_array($allowed)) {
                return (bool) $allowed[0];
            }
        }

        /**
         * Check if there is a common rule for that access
         */
        $sql = 'SELECT allowed FROM ' .
            $this->options['accessList'] .
            ' WHERE roles_name = ? AND resources_name = ? AND access_name = ?';
        $allowed = $this->options['db']->fetchOne($sql, Db::FETCH_NUM, array($role, '*', $access));
        if (is_array($allowed)) {
            return (bool) $allowed[0];
        }

        /**
         * Return the default access action
         */

        return $this->_defaultAccess;
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
        $sql = 'SELECT COUNT(*) FROM ' .
            $this->options['resourcesAccesses'] .
            ' WHERE resources_name = ? AND access_name = ?';
        $exists = $this->options['db']->fetchOne($sql, null, array($resourceName, $accessName));
        if (!$exists[0]) {
            throw new Exception(
                "Access '" . $accessName . "' does not exist in resource '" . $resourceName . "' in ACL"
            );
        }

        /**
         * Update the access in access_list
         */
        $sql = 'SELECT COUNT(*) FROM ' .
            $this->options['accessList'] .
            ' WHERE roles_name = ? AND resources_name = ? AND access_name = ?';
        $exists = $this->options['db']->fetchOne($sql, null, array($roleName, $resourceName, $accessName));
        if (!$exists[0]) {
            $sql = 'INSERT INTO ' . $this->options['accessList'] . ' VALUES (?, ?, ?, ?)';
            $params = array($roleName, $resourceName, $accessName, $action);
        } else {
            $sql = 'UPDATE ' .
                $this->options['accessList'] .
                ' SET allowed = ? WHERE roles_name = ? AND resources_name = ? AND access_name = ?';
            $params = array($action, $roleName, $resourceName, $accessName);
        }

        $this->options['db']->execute($sql, $params);

        /**
         * Update the access '*' in access_list
         */
        $sql = 'SELECT COUNT(*) FROM ' .
            $this->options['accessList'] .
            ' WHERE roles_name = ? AND resources_name = ? AND access_name = ?';
        $exists = $this->options['db']->fetchOne($sql, null, array($roleName, $resourceName, '*'));
        if (!$exists[0]) {
            $sql = 'INSERT INTO ' . $this->options['accessList'] . ' VALUES (?, ?, ?, ?)';
            $this->options['db']->execute($sql, array($roleName, $resourceName, '*', $this->_defaultAccess));
        }

        return true;
    }

    /**
     * Inserts/Updates a permission in the access list
     *
     * @param  string                 $roleName
     * @param  string                 $resourceName
     * @param  array|string           $access
     * @param  integer                $action
     * @throws \Phalcon\Acl\Exception
     */
    protected function allowOrDeny($roleName, $resourceName, $access, $action)
    {
        if (!$this->isRole($roleName)) {
            throw new Exception('Role "' . $roleName . '" does not exist in the list');
        }

        if (is_array($access)) {
            foreach ($access as $accessName) {
                $this->insertOrUpdateAccess($roleName, $resourceName, $accessName, $action);
            }
        } else {
            $this->insertOrUpdateAccess($roleName, $resourceName, $access, $action);
        }
    }
}
