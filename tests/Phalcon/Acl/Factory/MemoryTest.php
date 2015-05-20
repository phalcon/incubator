<?php

namespace Phalcon\Test\Acl\Factory;

class MemoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryShouldCreateMemoryAclObjectFromAclConfigurationWithAllOptions()
    {
        $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/_fixtures/acl.ini');
        $factory = new \Phalcon\Acl\Factory\Memory();
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
        $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/_fixtures/acl.ini');
        unset($config->acl->defaultAction);
        $factory = new \Phalcon\Acl\Factory\Memory();
        $acl = $factory->create($config->get('acl'));
    }

    /**
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Key "resource" must exist and must be traversable.
     */
    public function testFactoryShouldThrowExceptionIfResourceOptionIsMissing()
    {
        $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/_fixtures/acl.ini');
        unset($config->acl->resource);
        $factory = new \Phalcon\Acl\Factory\Memory();
        $acl = $factory->create($config->get('acl'));
    }

    /**
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Invalid value for accessList
     */
    public function testFactoryShouldThrowExceptionIfActionsKeyIsMissing()
    {
        if (version_compare(\Phalcon\Version::get(), '2.0.0', '=')) {
            $this->markTestSkipped('Fails due to a bug in Phalcon. See https://github.com/phalcon/cphalcon/pull/10226');
        }

        $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/_fixtures/acl.ini');
        unset($config->acl->resource->index->actions);
        unset($config->acl->role->guest->allow->index->actions[0]);
        $factory = new \Phalcon\Acl\Factory\Memory();
        $acl = $factory->create($config->get('acl'));
    }

    /**
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Key "role" must exist and must be traversable.
     */
    public function testFactoryShouldThrowExceptionIfRoleKeyIsMissing()
    {
        $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/_fixtures/acl.ini');
        unset($config->acl->role);
        $factory = new \Phalcon\Acl\Factory\Memory();
        $acl = $factory->create($config->get('acl'));
    }

    /**
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Wrong access method given. Expected "allow" or "deny" but "wrongmethod" was set.
     */
    public function testFactoryShouldThrowExceptionIfWrongMethodIsSet()
    {
        $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/_fixtures/acl.ini');
        $config->acl->role->user->wrongmethod = new \Phalcon\Config(array(
            'test' => array(
                'actions' => array(
                    'test',
                ),
            )
        ));
        $factory = new \Phalcon\Acl\Factory\Memory();
        $acl = $factory->create($config->get('acl'));
    }

    /**
     * @expectedException \Phalcon\Acl\Exception
     * @expectedExceptionMessage Role "user" cannot inherit non-existent role "nonexistentrole".
    Either such role does not exist or it is set to be inherited before it is actually defined.
     */
    public function testFactoryShouldThrowExceptionIfNonExistentInheritRoleIsSet()
    {
        $config = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/_fixtures/acl.ini');
        $config->acl->role->user->inherit = 'nonexistentrole';
        $factory = new \Phalcon\Acl\Factory\Memory();
        $acl = $factory->create($config->get('acl'));
    }

    public function testFactoryShouldWorkIfCreatedFromConfigPHPArray()
    {
        $factory = new \Phalcon\Acl\Factory\Memory();
        $acl = $factory->create(new \Phalcon\Config(include __DIR__ . '/_fixtures/acl.php'));
    }

    protected function assertAclIsConfiguredAsExpected(\Phalcon\Acl\Adapter\Memory $acl, \Phalcon\Config $config)
    {
        // assert default action
        $this->assertEquals(\Phalcon\Acl::DENY, $acl->getDefaultAction());

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
