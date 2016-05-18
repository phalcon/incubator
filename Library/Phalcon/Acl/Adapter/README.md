# Phalcon\Acl\Adapter

Usage examples of the adapters available here:

## Database

This adapter uses a database to store the ACL list:

```php
use Phalcon\Acl\Adapter\Database as AclDb;
use Phalcon\Db\Adapter\Pdo\Sqlite;

$connection = new Sqlite(['dbname' => 'sample.db']);

$acl = AclDb(
  [
    'db'                => $connection,
    'roles'             => 'roles',
    'rolesInherits'     => 'roles_inherits',
    'resources'         => 'resources',
    'resourcesAccesses' => 'resources_accesses',
    'accessList'        => 'access_list'
  ]
);

```

This adapter uses the following table to store the data:

```sql
CREATE TABLE `roles` (
  `name` VARCHAR(32) NOT NULL,
  `description` TEXT,
  PRIMARY KEY(`name`)
);

CREATE TABLE `access_list` (
  `roles_name` VARCHAR(32) NOT NULL,
  `resources_name` VARCHAR(32) NOT NULL,
  `access_name` VARCHAR(32) NOT NULL,
  `allowed` INT(3) NOT NULL,
  PRIMARY KEY(`roles_name`, `resources_name`, `access_name`)
);

CREATE TABLE `resources` (
  `name` VARCHAR(32) NOT NULL,
  `description` TEXT,
  PRIMARY KEY(`name`)
);

CREATE TABLE `resources_accesses` (
  `resources_name` VARCHAR(32) NOT NULL,
  `access_name` VARCHAR(32) NOT NULL,
  PRIMARY KEY(`resources_name`, `access_name`)
);

CREATE TABLE `roles_inherits` (
  `roles_name` VARCHAR(32) NOT NULL,
  `roles_inherit` VARCHAR(32) NOT NULL,
  PRIMARY KEY(roles_name, roles_inherit)
);
```

Using the cache adapter:

```php

// By default the action is deny access
$acl->setDefaultAction(Phalcon\Acl::DENY);

// You can add roles/resources/accesses to list or insert them directly in the tables

// Add roles
$acl->addRole(new Phalcon\Acl\Role('Admins'));

// Create the resource with its accesses
$acl->addResource('Products', array('insert', 'update', 'delete'));

// Allow Admins to insert products
$acl->allow('Admin', 'Products', 'insert');

// Do Admins are allowed to insert Products?
var_dump($acl->isAllowed('Admins', 'Products', 'update'));

```
