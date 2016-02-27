<?php

namespace Phalcon\Test\Mvc\Model\Behavior;

use Mockery;
use Phalcon\Di;
use Phalcon\Mvc\ModelInterface;
use UnitTester;
use ReflectionProperty;
use CategoriesManyRoots;
use Phalcon\DiInterface;
use Codeception\Specify;
use Phalcon\Mvc\Model\Manager;
use Codeception\TestCase\Test;
use Phalcon\Mvc\Model\Metadata;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Codeception\Specify\Config as SpecifyConfig;
use Phalcon\Mvc\Model\Behavior\NestedSet as NestedSetBehavior;

/**
 * \Phalcon\Test\Mvc\Model\Behavior\Helper
 * Helper class for Phalcon\Test\Mvc\Model\Behavior tests
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @package   Phalcon\Test\Mvc\Model\Behavior
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class Helper extends Test
{
    use Specify;

    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var DiInterface
     */
    protected $previousDependencyInjector;

    /**
     * executed before each test
     */
    protected function _before()
    {
        require_once 'Stubs/Categories.php';

        $this->previousDependencyInjector = Di::getDefault();

        $di = new Di();

        $di->setShared('modelsMetadata', new Metadata\Memory());
        $di->setShared('modelsManager', new Manager());
        $di->setShared('db', function () {
            return new Mysql([
                'host' => TEST_DB_HOST,
                'port' => TEST_DB_PORT,
                'username' => TEST_DB_USER,
                'password' => TEST_DB_PASSWD,
                'dbname' => TEST_DB_NAME,
                'charset' => TEST_DB_CHARSET,
            ]);
        });

        if ($this->previousDependencyInjector instanceof DiInterface) {
            Di::setDefault($di);
        }

        SpecifyConfig::setDeepClone(false);

        $this->truncateTable(CategoriesManyRoots::$table);
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
        if ($this->previousDependencyInjector instanceof DiInterface) {
            Di::setDefault($this->previousDependencyInjector);
        } else {
            Di::reset();
        }
    }

    protected function getProperty($propertyName, NestedSetBehavior $behavior)
    {
        $property = new ReflectionProperty(get_class($behavior), $propertyName);
        $property->setAccessible(true);

        return $property->getValue($behavior);
    }

    /**
     * @return \Phalcon\Db\AdapterInterface
     */
    protected function getConnection()
    {
        return Di::getDefault()->getShared('db');
    }

    /**
     * @return \Pdo
     */
    protected function getDbPdo()
    {
        return $this->getModule('Db')->dbh;
    }

    protected function truncateTable($table)
    {
        $this->getDbPdo()->query("TRUNCATE TABLE `{$table}`")->execute();
        $this->getDbPdo()->query("ALTER TABLE `{$table}` AUTO_INCREMENT = 1")->execute();

        $this->tester->seeNumRecords(0, $table);
    }

    protected function prettifyRoots($multipleTrees = true)
    {
        if ($multipleTrees) {
            $order = 'root, lft';
        } else {
            $order = 'lft';
        }

        $categories = CategoriesManyRoots::find(['order' => $order]);

        $result = [];
        foreach ($categories as $category) {
            $result[] = str_repeat(' ', ($category->level - 1) * 5) . $category->name;
        }

        return $result;
    }

    /**
     * Checking the integrity of keys
     *
     * @param int|null $rootId
     */
    protected function checkIntegrity($rootId = null)
    {
        $connection = $this->getConnection();

        $sql = "SELECT COUNT(*) cnt FROM categories WHERE lft >= rgt";
        if ($rootId) {
            $sql .= " AND root = {$rootId}";
        }

        /** @var \Phalcon\Db\Result\Pdo $check1 */
        $check1 = $connection->query($sql);
        $this->assertEquals(['cnt' => '0'], $check1->fetch(\PDO::FETCH_ASSOC));


        $sql = "SELECT COUNT(*) cnt, MIN(lft) min, MAX(rgt) max FROM categories";
        if ($rootId) {
            $sql .= " WHERE root = {$rootId}";
        }

        /** @var \Phalcon\Db\Result\Pdo $check2 */
        $check2 = $connection->query($sql);
        $result = $check2->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals(1, $result['min']);
        $this->assertEquals($result['cnt'] * 2, $result['max']);

        $sql = "SELECT COUNT(*) cnt FROM categories WHERE MOD((rgt - lft), 2) = 0";
        if ($rootId) {
            $sql .= " AND root = {$rootId}";
        }

        /** @var \Phalcon\Db\Result\Pdo $check3 */
        $check3 = $connection->query($sql);
        $this->assertEquals(['cnt' => '0'], $check3->fetch(\PDO::FETCH_ASSOC));

        $sql = "SELECT COUNT(*) cnt FROM categories WHERE MOD((lft - level + 2), 2) = 1";
        if ($rootId) {
            $sql .= " AND root = {$rootId}";
        }

        /** @var \Phalcon\Db\Result\Pdo $check4 */
        $check4 = $connection->query($sql);
        $this->assertEquals(['cnt' => '0'], $check4->fetch(\PDO::FETCH_ASSOC));
    }

    protected function createTree()
    {
        $cars = new CategoriesManyRoots();
        $cars->name = 'Cars';
        $cars->saveNode();

        $ford = new CategoriesManyRoots();
        $ford->name = 'Ford';

        $audi = new CategoriesManyRoots();
        $audi->name = 'Audi';

        $mercedes = new CategoriesManyRoots();
        $mercedes->name = 'Mercedes';

        $ford->appendTo($cars);
        $mercedes->insertAfter($ford);
        $audi->insertBefore($ford);
        $phones = new CategoriesManyRoots();
        $phones->name = 'Mobile Phones';
        $phones->saveNode();

        $samsung = new CategoriesManyRoots();
        $samsung->name = 'Samsung';

        $motorola = new CategoriesManyRoots();
        $motorola->name = 'Motorola';

        $iphone = new CategoriesManyRoots();
        $iphone->name = 'iPhone';

        $samsung->appendTo($phones);
        $motorola->insertAfter($samsung);
        $iphone->prependTo($phones);
    }
}
