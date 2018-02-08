<?php
namespace Phalcon\Test\Test\PHPUnit;

use Codeception\Test\Unit;
use Phalcon\Test\PHPUnit\ModelTestCase;
use Phalcon\Mvc\Model\Manager as PhModelManager;

/**
 * \Phalcon\Test\Test\PHPUnit\ModelTestCaseTest. Tests Integration With Trait.
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Phoenix Osiris <phoenix@twistersfury.com>
 * @package   Phalcon\Test\Test\Traits
 * @group     Acl
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class ModelTestCaseTest extends Unit
{
    public function testUsesTrait()
    {
        /** @var ModelTestCase $testSubject */
        $testSubject = $this->getMockBuilder(ModelTestCase::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testSubject->setUp();

        $reflectionProperty = new \ReflectionProperty(ModelTestCase::class, 'di');
        $reflectionProperty->setAccessible(true);

        $this->assertInstanceOf(
            PhModelManager::class,
            $reflectionProperty->getValue($testSubject)->get('modelsManager')
        );
    }
}
