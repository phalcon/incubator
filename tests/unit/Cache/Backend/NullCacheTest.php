<?php

namespace Phalcon\Test\Cache\Backend;

use Phalcon\Cache\Backend\NullCache;
use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Cache\Frontend\None as NoneFrontend;

/**
 * \Phalcon\Test\Cache\Backend\DatabaseTest
 * Tests for Phalcon\Cache\Backend\Database component
 *
 * @copyright (c) 2011-2018 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Nikita Vershinin <endeveit@gmail.com>
 * @package   Phalcon\Test\Cache\Backend
 * @group     db
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class NullCacheTest extends Test
{
    public function testStartShouldAlwaysReturnTrue()
    {
        $nullCache = new NullCache();

        $this->assertTrue(
            $nullCache->start('fooBar')
        );

        $this->assertTrue(
            $nullCache->start('fooBar'),
            1000
        );
    }

    public function testFrontendShouldBeNone()
    {
        $nullCache = new NullCache();

        $this->assertInstanceOf(
            NoneFrontend::class,
            $nullCache->getFrontend()
        );
    }

    public function testGetOptionsShouldReturnEmptyArray()
    {
        $nullCache = new NullCache();

        $this->assertEquals(
            [],
            $nullCache->getOptions()
        );
    }

    public function testCacheShouldAlwaysBeFresh()
    {
        $nullCache = new NullCache();

        $this->assertTrue(
            $nullCache->isFresh()
        );
    }

    public function testCacheShouldAlwaysBeStarted()
    {
        $nullCache = new NullCache();

        $this->assertTrue(
            $nullCache->isStarted()
        );

        $nullCache->start('fooBar');

        $this->assertTrue(
            $nullCache->isStarted()
        );

        $nullCache->stop();

        $this->assertTrue(
            $nullCache->isStarted()
        );
    }

    public function testLastKeyShouldBeEmpty()
    {
        $nullCache = new NullCache();

        $this->assertEquals(
            '',
            $nullCache->getLastKey()
        );
    }

    public function testGetSomethingFromCacheShouldAlwaysReturnNull()
    {
        $nullCache = new NullCache();

        $this->assertEquals(
            null,
            $nullCache->get('fooBar')
        );

        $this->assertEquals(
            null,
            $nullCache->get('fooBar', 1000)
        );

        $this->assertEquals(
            null,
            $nullCache->get('fooBar', 0)
        );

        $nullCache->save('fooBar', 'baz', 1000);

        $this->assertEquals(
            null,
            $nullCache->get('fooBar')
        );

        $this->assertEquals(
            null,
            $nullCache->get('fooBar', 1000)
        );

        $this->assertEquals(
            null,
            $nullCache->get('fooBar', 0)
        );
    }

    public function testSaveSomethingToCacheShouldAlwaysReturnTrue()
    {
        $nullCache = new NullCache();

        $this->assertTrue(
            $nullCache->save('fooBar', null)
        );

        $this->assertTrue(
            $nullCache->save('fooBar', 'baz')
        );

        $this->assertTrue(
            $nullCache->save('fooBar', 'baz', 1000)
        );

        $this->assertTrue(
            $nullCache->save('fooBar', 'baz', 1000, true)
        );

        $this->assertTrue(
            $nullCache->save('fooBar', 'baz', 1000, false)
        );
    }

    public function testDeleteSomethingFromCacheShouldAlwaysReturnTrue()
    {
        $nullCache = new NullCache();

        $this->assertTrue(
            $nullCache->delete('fooBar')
        );

        $this->assertFalse(
            $nullCache->exists('fooBar')
        );

        $randomKey = 'randomKey' . uniqid('NullCache', true);

        $this->assertTrue(
            $nullCache->delete($randomKey)
        );

        $this->assertFalse(
            $nullCache->exists($randomKey)
        );
    }

    public function testQueryKeysShouldReturnEmptyArray()
    {
        $nullCache = new NullCache();

        $this->assertEquals(
            [],
            $nullCache->queryKeys()
        );

        $this->assertEquals(
            [],
            $nullCache->queryKeys('fooBar')
        );
    }

    public function testNoKeyWillEverExistsInTheCache()
    {
        $nullCache = new NullCache();

        $this->assertFalse(
            $nullCache->exists('fooBar')
        );

        $this->assertTrue(
            $nullCache->save('fooBar', 'baz')
        );

        $this->assertFalse(
            $nullCache->exists('fooBar')
        );

        $randomKey = 'randomKey' . uniqid('NullCache', true);

        $this->assertTrue(
            $nullCache->save('fooBar', $randomKey)
        );

        $this->assertFalse(
            $nullCache->exists($randomKey)
        );
    }
}
