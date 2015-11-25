<?php

namespace Phalcon\Test\Acl\Factory;

use Phalcon\Acl;
use Phalcon\Acl\Adapter\Memory as MemoryAdapter;
use Phalcon\Acl\Factory\Memory as MemoryFactory;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Config;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Test\Acl\Factory\MemoryTest
 * Tests for Phalcon\Acl\Factory\Memory component
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Nemanja Ognjanovic <nemanja@ognjanovic.me>
 * @package   Phalcon\Test\Acl\Factory
 * @group     Acl
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class MemoryTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    /**
     * executed before each test
     */
    protected function _before()
    {
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    public function testFactoryShouldCreateMemoryAclObjectFromAclConfigurationWithAllOptions()
    {
        $config = new Ini(INCUBATOR_FIXTURES . 'Acl/acl.ini');
        $factory = new MemoryFactory();
        $acl = $factory->create($config->get('acl'));

        $this->assertInstanceOf('Phalcon\Acl\Adapter\Memory', $acl);
        $this->assertAclIsConfiguredAsExpected($acl, $config);
    }

    public function testFactoryShouldWorkIfCreatedFromConfigPHPArray()
    {
        $config = new Config(include INCUBATOR_FIXTURES . 'Acl/acl.php');
        $factory = new MemoryFactory();
        $acl = $factory->create($config->get('acl'));

        $this->assertInstanceOf('Phalcon\Acl\Adapter\Memory', $acl);
        $this->assertAclIsConfiguredAsExpected($acl, $config);
    }

    /**
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Key "defaultAction" must exist and must be of numeric value.
     */
    public function testFactoryShouldThrowExceptionIfDefaultActionIsMissing()
    {
        $config = new Ini(INCUBATOR_FIXTURES . 'Acl/acl.ini');
        unset($config->acl->defaultAction);
        $factory = new MemoryFactory();
        $factory->create($config->get('acl'));
    }

    /**
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Key "resource" must exist and must be traversable.
     */
    public function testFactoryShouldThrowExceptionIfResourceOptionIsMissing()
    {
        $config = new Ini(INCUBATOR_FIXTURES . 'Acl/acl.ini');
        unset($config->acl->resource);
        $factory = new MemoryFactory();
        $factory->create($config->get('acl'));
    }

    /**
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Invalid value for accessList
     */
    public function testFactoryShouldThrowExceptionIfActionsKeyIsMissing()
    {
        $config = new Ini(INCUBATOR_FIXTURES . 'Acl/acl.ini');
        unset($config->acl->resource->index->actions);
        unset($config->acl->role->guest->allow->index->actions[0]);
        $factory = new MemoryFactory();
        $factory->create($config->get('acl'));
    }

    /**
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Key "role" must exist and must be traversable.
     */
    public function testFactoryShouldThrowExceptionIfRoleKeyIsMissing()
    {
        $config = new Ini(INCUBATOR_FIXTURES . 'Acl/acl.ini');
        unset($config->acl->role);
        $factory = new MemoryFactory();
        $factory->create($config->get('acl'));
    }

    /**
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Wrong access method given. Expected "allow" or "deny" but "wrongmethod" was set.
     */
    public function testFactoryShouldThrowExceptionIfWrongMethodIsSet()
    {
        $config = new Ini(INCUBATOR_FIXTURES . 'Acl/acl.ini');
        $config->acl->role->user->wrongmethod = new Config(['test' => ['actions' => ['test']]]);
        $factory = new MemoryFactory();
        $factory->create($config->get('acl'));
    }

    /**
     * @dataProvider invalidActionProvider
     *
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Key "actions" must exist and must be traversable.
     *
     * @param mixed $action
     */
    public function testFactoryShouldThrowExceptionIfWrongNoActionIsSet($action)
    {
        $config = new Ini(INCUBATOR_FIXTURES . 'Acl/acl.ini');
        $config->acl->role->user->wrongmethod = new Config(['test' => ['actions' => $action]]);
        $factory = new MemoryFactory();
        $factory->create($config->get('acl'));
    }

    public function invalidActionProvider()
    {
        return [
            'int'      => [PHP_INT_MAX],
            'float'    => [microtime(true)],
            'null'     => [null],
            'bool'     => [false],
            'object'   => [new \stdClass],
            'callable' => [function () {}],
        ];
    }

    /**
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Role "user" cannot inherit non-existent role "nonexistentrole".
     * Either such role does not exist or it is set to be inherited before it is actually defined.
     */
    public function testFactoryShouldThrowExceptionIfNonExistentInheritRoleIsSet()
    {
        $config = new Ini(INCUBATOR_FIXTURES . 'Acl/acl.ini');
        $config->acl->role->user->inherit = 'nonexistentrole';
        $factory = new MemoryFactory();
        $factory->create($config->get('acl'));
    }

    protected function assertAclIsConfiguredAsExpected(MemoryAdapter $acl, Config $config)
    {
        // assert default action
        $this->assertEquals(Acl::DENY, $acl->getDefaultAction());

        // assert resources
        $resources = $acl->getResources();
        $this->assertInternalType('array', $resources);
        $indexResource = $resources[0];
        $testResource = $resources[1];
        $this->assertEquals('index', $indexResource->getName());
        $this->assertEquals('test', $testResource->getName());
        $this->assertEquals($config->acl->resource->index->description, $indexResource->getDescription());
        $this->assertEquals($config->acl->resource->test->description, $testResource->getDescription());

        // assert roles
        $roles = $acl->getRoles();
        $this->assertInternalType('array', $roles);
        $guestRole = $roles[0];
        $userRole = $roles[1];
        $this->assertEquals('guest', $guestRole->getName());
        $this->assertEquals('user', $userRole->getName());
        $this->assertEquals($config->acl->role->guest->description, $guestRole->getDescription());
        $this->assertEquals($config->acl->role->user->description, $userRole->getDescription());

        // assert guest rules
        $this->assertTrue($acl->isAllowed('guest', 'index', 'index'));
        $this->assertFalse($acl->isAllowed('guest', 'test', 'index'));

        // assert user rules
        // inherited from guest
        $this->assertTrue($acl->isAllowed('user', 'index', 'index'));
        $this->assertTrue($acl->isAllowed('user', 'test', 'index'));
    }
}
