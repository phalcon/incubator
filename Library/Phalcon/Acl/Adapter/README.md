
Phalcon\Acl\Adapter
===================

Usage examples of the adapters available here:

Database
--------
This adapter uses a database to store the ACL list:

```php

$connection = new \Phalcon\Db\Adapter\Pdo\Sqlite(array(
    "dbname" => "sample.db"
));

$acl = new Phalcon\Acl\Adapter\Database(array(
	'db' => $connection,
	'roles' => 'roles',
	'rolesInherits' => 'roles_inherits',
	'resources' => 'resources',
	'resourcesAccesses' => 'resources_accesses',
	'accessList' => 'access_list',
));

```

This adapter uses the following table to store the data:

```sql
CREATE TABLE roles (name varchar(32) not null, description text, primary key(name));
CREATE TABLE access_list (roles_name varchar(32) not null, resources_name varchar(32) not null, access_name varchar(32) not null, allowed int(3) not null, primary key(roles_name, resources_name, access_name));
CREATE TABLE resources (name varchar(32) not null, description text, primary key(name));
CREATE TABLE resources_accesses (resources_name varchar(32) not null, access_name varchar(32) not null, primary key(resources_name, access_name));
CREATE TABLE roles_inherits (roles_name varchar(32) not null, roles_inherit varchar(32) not null, primary key(roles_name, roles_inherit));
```

Using the cache adapter:

```php

//By default the action is deny access
$acl->setDefaultAction(Phalcon\Acl::DENY);

//You can add roles/resources/accesses to list or insert them directly in the tables

//Add roles
$acl->addRole(new Phalcon\Acl\Role('Admins'));

//Create the resource with its accesses
$acl->addResource('Products', array('insert', 'update', 'delete'));

//Allow Admins to insert products
$acl->allow('Admin', 'Products', 'insert');

//Do Admins are allowed to insert Products?
var_dump($acl->isAllowed('Admins', 'Products', 'update'));

```
