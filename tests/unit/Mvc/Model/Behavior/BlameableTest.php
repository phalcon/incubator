<?php

namespace Phalcon\Test\Mvc\Model\Behavior;

use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Model\Behavior\Blameable\Audit;
use Phalcon\Mvc\Model\Behavior\Blameable\AuditDetail;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Test\Models\Robots;

/**
 * \Phalcon\Test\Mvc\Model\Behavior\NestedSetTest
 * Tests for Phalcon\Mvc\Model\Behavior\NestedSet component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @author    Wojciech Ślawski <jurigag@gmail.com>
 * @package   Phalcon\Test\Mvc\Model\Behavior
 * @group     db
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class BlameableTest extends Test
{
    protected $di;

    protected $defaultDi;

    public function _before()
    {
        $di = new FactoryDefault();
        $di->set(
            'db',
            function () {
                $adapter = new Mysql(
                    [
                        'host'     => env('TEST_DB_HOST', '127.0.0.1'),
                        'username' => env('TEST_DB_USER', 'incubator'),
                        'password' => env('TEST_DB_PASSWD', 'secret'),
                        'dbname'   => env('TEST_DB_NAME', 'incubator'),
                        'charset'  => env('TEST_DB_CHARSET', 'utf8'),
                        'port'     => env('TEST_DB_PORT', 3306),
                    ]
                );

                return $adapter;
            }
        );
        $this->di = $di;
        $this->defaultDi = Di::getDefault();
        Di::setDefault($this->di);
    }

    public function testCreate()
    {
        $robots = new Robots();
        $robots->name = 'Astro Boy';
        $robots->type = 'mechanical';
        $this->assertTrue($robots->create());
        $audit = Audit::findFirst();
        $this->assertNotEmpty($audit);
        $this->assertEquals(Robots::class, $audit->model_name);
        $this->assertNotEmpty($audit->primary_key);
        $this->assertEquals($audit->primary_key[0], $robots->id);
        $this->assertEquals('C', $audit->type);
        $this->assertEquals('127.0.0.1', $audit->ipaddress);
        /** @var AuditDetail[]|Resultset $details */
        $details = $audit->details->toArray();
        $this->assertEquals(
            [
                [
                    'id'         => 1,
                    'audit_id'   => 1,
                    'field_name' => 'id',
                    'old_value'  => null,
                    'new_value'  => 1,
                ],
                [
                    'id'         => 2,
                    'audit_id'   => 1,
                    'field_name' => 'name',
                    'old_value'  => null,
                    'new_value'  => 'Astro Boy',
                ],
                [
                    'id'         => 3,
                    'audit_id'   => 1,
                    'field_name' => 'type',
                    'old_value'  => null,
                    'new_value'  => 'mechanical',
                ],
            ],
            $details
        );
    }

    public function testUpdate()
    {
        $robots = Robots::findFirst();
        $robots->type = 'hydraulic';
        $this->assertTrue($robots->update());
        $audit = Audit::findFirst(2);
        $this->assertNotEmpty($audit);
        $this->assertEquals(Robots::class, $audit->model_name);
        $this->assertNotEmpty($audit->primary_key);
        $this->assertEquals($audit->primary_key[0], $robots->id);
        $this->assertEquals('U', $audit->type);
        $this->assertEquals('127.0.0.1', $audit->ipaddress);
        /** @var AuditDetail[]|Resultset $details */
        $details = $audit->details->toArray();
        $this->assertEquals(
            [
                [
                    'id'         => 4,
                    'audit_id'   => 2,
                    'field_name' => 'type',
                    'old_value'  => 'mechanical',
                    'new_value'  => 'hydraulic',
                ],
            ],
            $details
        );
    }

    public function _after()
    {
        Di::setDefault($this->defaultDi);
    }
}
