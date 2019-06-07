<?php

namespace Phalcon\Test\Acl\Adapter;

use Phalcon\Db\AdapterInterface as DbAdapter;
use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Acl\Adapter\Database;
use Phalcon\Test\Codeception\UnitTestCase as Test;
use ReflectionProperty;

/**
 * \Phalcon\Test\Acl\Adapter\DatabaseTest
 * Tests for Phalcon\Acl\Adapter\Database component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @package   Phalcon\Test\Acl\Adapter
 * @group     Acl
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class DatabaseTest extends Test
{
    const ADAPTER_CLASS = Database::class;

    protected function getConnection()
    {
        return new Sqlite(
            [
                'dbname' => 'tests/_output/sample.db',
            ]
        );
    }

    protected function assertProtectedPropertyEquals($propertyName, $tableName, DbAdapter $connection, Database $adapter)
    {
        $property = new ReflectionProperty(
            self::ADAPTER_CLASS,
            $propertyName
        );

        $property->setAccessible(true);

        $this->assertEquals(
            $connection->escapeIdentifier($tableName),
            $property->getValue($adapter)
        );
    }

    /**
     * @param array $options
     *
     * @dataProvider incorrectDbProvider
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Parameter "db" is required and it must be an instance of Phalcon\Acl\AdapterInterface
     */
    public function testShouldThrowExceptionIfDbIsMissingOrInvalid($options)
    {
        new Database($options);
    }

    public function incorrectDbProvider()
    {
        return [
            [['abc' => '']],
            [['db'  => null]],
            [['db'  => true]],
            [['db'  => __CLASS__]],
            [['db'  => new \stdClass()]],
            [['db'  => []]],
            [['db'  => microtime(true)]],
            [['db'  => PHP_INT_MAX]],
        ];
    }

    /**
     * @param string $expected
     * @param array $options
     *
     * @dataProvider incorrectOptionsProvider
     */
    public function testShouldThrowExceptionWhenOptionsIsInvalid($expected, $options)
    {
        $this->tester->setExpectedException(
            '\Phalcon\Acl\Exception',
            "Parameter '{$expected}' is required and it must be a non empty string"
        );

        new Database($options);
    }

    public function incorrectOptionsProvider()
    {
        return [
            ['roles', ['db' => $this->getConnection()]],
            ['roles', ['db' => $this->getConnection(), 'roles' => '']],
            ['roles', ['db' => $this->getConnection(), 'roles' => true]],
            ['roles', ['db' => $this->getConnection(), 'roles' => []]],

            ['resources', ['db' => $this->getConnection(), 'roles' => 'roles']],
            ['resources', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => '']],
            ['resources', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => true]],
            ['resources', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => []]],

            ['resourcesAccesses', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources']],
            ['resourcesAccesses', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources', 'resourcesAccesses' => '']],
            ['resourcesAccesses', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources', 'resourcesAccesses' => true]],
            ['resourcesAccesses', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources', 'resourcesAccesses' => []]],

            ['accessList', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources', 'resourcesAccesses' => 'resources_accesses']],
            ['accessList', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources', 'resourcesAccesses' => 'resources_accesses', 'accessList' => '']],
            ['accessList', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources', 'resourcesAccesses' => 'resources_accesses', 'accessList' => true]],
            ['accessList', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources', 'resourcesAccesses' => 'resources_accesses', 'accessList' => []]],

            ['rolesInherits', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources', 'resourcesAccesses' => 'resources_accesses', 'accessList' => 'access_list']],
            ['rolesInherits', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources', 'resourcesAccesses' => 'resources_accesses', 'accessList' => 'access_list', 'rolesInherits' => '']],
            ['rolesInherits', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources', 'resourcesAccesses' => 'resources_accesses', 'accessList' => 'access_list', 'rolesInherits' => true]],
            ['rolesInherits', ['db' => $this->getConnection(), 'roles' => 'roles', 'resources' => 'resources', 'resourcesAccesses' => 'resources_accesses', 'accessList' => 'access_list', 'rolesInherits' => []]],
        ];
    }

    public function testShouldCreateAdapterInstance()
    {
        $connection = $this->getConnection();

        $options = [
            'db'                => $connection,
            'roles'             => 'roles',
            'rolesInherits'     => 'roles_inherits',
            'resources'         => 'resources',
            'resourcesAccesses' => 'resources_accesses',
            'accessList'        => 'access_list',
        ];

        $adapter = new Database($options);

        $this->assertInstanceOf(
            self::ADAPTER_CLASS,
            $adapter
        );

        unset($options['db']);

        foreach ($options as $property => $tableName) {
            $this->assertProtectedPropertyEquals(
                $property,
                $tableName,
                $connection,
                $adapter
            );
        }
    }
}
