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

use Phalcon\Db;
use Phalcon\Db\AdapterInterface as DbAdapter;
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
     * @var DbAdapter
     */
    protected $connection;

    /**
     * Roles table
     * @var string
     */
    protected $roles;

    /**
     * Resources table
     * @var string
     */
    protected $resources;

    /**
     * Resources Accesses table
     * @var string
     */
    protected $resourcesAccesses;

    /**
     * Access List table
     * @var string
     */
    protected $accessList;

    /**
     * Roles Inherits table
     * @var string
     */
    protected $rolesInherits;

    /**
     * Class constructor.
     *
     * @param  array $options Adapter config
     * @throws Exception
     */
    public function __construct(array $options)
    {
        if (!isset($options['db']) || !$options['db'] instanceof DbAdapter) {
            throw new Exception('Parameter "db" is required and it must be instance of Phalcon\Acl\AdapterInterface');
        }

        $this->connection = $options['db'];

        foreach (['roles', 'resources', 'resourcesAccesses', 'accessList', 'rolesInherits'] as $table) {
            if (!isset($options[$table]) || empty($options[$table]) || !is_string($options[$table])) {
                throw new Exception("Parameter '{$table}' is required and it must be non empty string");
            }

            $this->{$table} = $this->connection->escapeIdentifier($options[$table]);
        }
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

        $exists = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM {$this->roles} WHERE name = ?",
            null,
            [$role->getName()]
        );

        if (!$exists[0]) {
            $this->connection->execute(
                "INSERT INTO {$this->roles} VALUES (?, ?)",
                [$role->getName(), $role->getDescription()]
            );

            $this->connection->execute(
                "INSERT INTO {$this->accessList} VALUES (?, ?, ?, ?)",
                [$role->getName(), '*', '*', $this->_defaultAccess]
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
        $sql = "SELECT COUNT(*) FROM {$this->roles} WHERE name = ?";
        $exists = $this->connection->fetchOne($sql, null, [$roleName]);
        if (!$exists[0]) {
            throw new Exception("Role '{$roleName}' does not exist in the role list");
        }

        $exists = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM {$this->rolesInherits} WHERE roles_name = ? AND roles_inherit = ?",
            null,
            [$roleName, $roleToInherit]
        );

        if (!$exists[0]) {
            $this->connection->execute(
                "INSERT INTO {$this->rolesInherits} VALUES (?, ?)",
                [$roleName, $roleToInherit]
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
        $exists = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM {$this->roles} WHERE name = ?",
            null,
            [$roleName]
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
        $exists = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM {$this->resources} WHERE name = ?",
            null,
            [$resourceName]
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
     * $acl->addResource(new Phalcon\Acl\Resource('customers'), ['create', 'search']);
     * $acl->addResource('customers', ['create', 'search']);
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

        $exists = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM {$this->resources} WHERE name = ?",
            null,
            [$resource->getName()]
        );

        if (!$exists[0]) {
            $this->connection->execute(
                "INSERT INTO {$this->resources} VALUES (?, ?)",
                [$resource->getName(), $resource->getDescription()]
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
            throw new Exception("Resource '{$resourceName}' does not exist in ACL");
        }

        $sql = "SELECT COUNT(*) FROM {$this->resourcesAccesses} WHERE resources_name = ? AND access_name = ?";

        if (!is_array($accessList)) {
            $accessList = [$accessList];
        }

        foreach ($accessList as $accessName) {
            $exists = $this->connection->fetchOne($sql, null, [$resourceName, $accessName]);
            if (!$exists[0]) {
                $this->connection->execute(
                    'INSERT INTO ' . $this->resourcesAccesses . ' VALUES (?, ?)',
                    [$resourceName, $accessName]
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
        $resources = [];
        $sql       = "SELECT * FROM {$this->resources}";

        foreach ($this->connection->fetchAll($sql, Db::FETCH_ASSOC) as $row) {
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
        $roles = [];
        $sql   = "SELECT * FROM {$this->roles}";

        foreach ($this->connection->fetchAll($sql, Db::FETCH_ASSOC) as $row) {
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
     * $acl->allow('guests', 'customers', ['search', 'create']);
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
     * $acl->deny('guests', 'customers', ['search', 'create']);
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
        $sql = implode(' ', [
            "SELECT 'allowed' FROM {$this->accessList} AS a",
            // role_name in:
            'WHERE roles_name IN (',
                // given 'role'-parameter
                'SELECT ? ',
                // inherited role_names
                "UNION SELECT roles_inherit FROM {$this->rolesInherits} WHERE roles_name = ?",
                // or 'any'
                "UNION SELECT '*'",
            ')',
            // resources_name should be given one or 'any'
            "AND resources_name IN (?, '*')",
            // access_name should be given one or 'any'
            "AND access_name IN (?, '*')",
            // order be the sum of bools for 'literals' before 'any'
            "ORDER BY (roles_name != '*')+(resources_name != '*')+(access_name != '*') DESC",
            // get only one...
            'LIMIT 1'
        ]);

        // fetch one entry...
        $allowed = $this->connection->fetchOne($sql, Db::FETCH_NUM, [$role, $role, $resource, $access]);
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
        $sql = "SELECT COUNT(*) FROM {$this->resourcesAccesses} WHERE resources_name = ? AND access_name = ?";
        $exists = $this->connection->fetchOne($sql, null, [$resourceName, $accessName]);
        if (!$exists[0]) {
            throw new Exception(
                "Access '{$accessName}' does not exist in resource '{$resourceName}' in ACL"
            );
        }

        /**
         * Update the access in access_list
         */
        $sql = "SELECT COUNT(*) FROM {$this->accessList} "
            . " WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
        $exists = $this->connection->fetchOne($sql, null, [$roleName, $resourceName, $accessName]);
        if (!$exists[0]) {
            $sql = "INSERT INTO {$this->accessList} VALUES (?, ?, ?, ?)";
            $params = [$roleName, $resourceName, $accessName, $action];
        } else {
            $sql = "UPDATE {$this->accessList} SET allowed = ? " .
                "WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
            $params = [$action, $roleName, $resourceName, $accessName];
        }

        $this->connection->execute($sql, $params);

        /**
         * Update the access '*' in access_list
         */
        $sql = "SELECT COUNT(*) FROM {$this->accessList} " .
            "WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
        $exists = $this->connection->fetchOne($sql, null, [$roleName, $resourceName, '*']);
        if (!$exists[0]) {
            $sql = "INSERT INTO {$this->accessList} VALUES (?, ?, ?, ?)";
            $this->connection->execute($sql, [$roleName, $resourceName, '*', $this->_defaultAccess]);
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
            throw new Exception("Role '{$roleName}' does not exist in the list");
        }

        if (!is_array($access)) {
            $access = [$access];
        }

        foreach ($access as $accessName) {
            $this->insertOrUpdateAccess($roleName, $resourceName, $accessName, $action);
        }
    }
}
