<?php

namespace Phalcon\Test\Mvc\Model\Behavior;

use CategoriesManyRoots;
use CategoriesOneRoot;
use Phalcon\Mvc\Model\Behavior\NestedSet as NestedSetBehavior;

/**
 * \Phalcon\Test\Mvc\Model\Behavior\NestedSetTest
 * Tests for Phalcon\Mvc\Model\Behavior\NestedSet component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
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
class NestedSetTest extends Helper
{
    /**
     * Initialize NestedSet Behavior without params
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-02-27
     */
    public function testShouldCreateNestedSetBehaviorInstanceWithNoParams()
    {
        $this->specify(
            'Unable to initialize NestedSet Behavior without params correctly',
            function ($property, $expected) {
                $behavior = new NestedSetBehavior;
                expect($this->getProperty($property, $behavior))->equals($expected);
            },
            ['examples' => [
                ['db', null],
                ['owner', null],
                ['hasManyRoots', false],
                ['rootAttribute', 'root'],
                ['leftAttribute', 'lft'],
                ['rightAttribute', 'rgt'],
                ['rootAttribute', 'root'],
                ['levelAttribute', 'level'],
                ['primaryKey', 'id'],
                ['ignoreEvent', false],
                ['deleted', false],
            ]]
        );
    }

    /**
     * Initialize NestedSet Behavior with desired params
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-02-27
     */
    public function testShouldCreateNestedSetBehaviorInstanceWithDesiredParams()
    {
        $this->specify(
            'Unable to initialize NestedSet Behavior with desired params correctly',
            function ($property, $value) {
                $behavior = new NestedSetBehavior([$property => $value]);
                expect($this->getProperty($property, $behavior))->equals($value);
            },
            ['examples' => [
                ['leftAttribute', 'left'],
                ['rightAttribute', 'right'],
                ['rootAttribute', 'main'],
                ['levelAttribute', 'lvl'],
                ['hasManyRoots', true],
                ['primaryKey', 'pk'],
                ['db', $this->getConnection()],
            ]]
        );
    }

    /**
     * Creating root nodes
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-02-27
     */
    public function testShouldCreateARootNodeUsingSaveNode()
    {
        $this->specify(
            'Unable to create a root node using NestedSet::saveNode',
            function () {
                $I = $this->tester;

                $I->seeNumRecords(0, CategoriesManyRoots::$table);

                $category1 = new CategoriesManyRoots();
                $category1->name = 'Mobile Phones';
                $category1->saveNode();

                $I->seeInDatabase(CategoriesManyRoots::$table, ['name' => 'Mobile Phones']);

                $category1 = CategoriesManyRoots::findFirst();

                expect($category1->root)->equals(1);
                expect($category1->lft)->equals(1);
                expect($category1->rgt)->equals(2);
                expect($category1->level)->equals(1);

                $category2 = new CategoriesManyRoots();
                $category2->name = 'Cars';
                $category2->saveNode();

                $I->seeInDatabase(CategoriesManyRoots::$table, ['name' => 'Cars']);

                $category2 = CategoriesManyRoots::findFirst(2);

                expect($category2->root)->equals(2);
                expect($category2->lft)->equals(1);
                expect($category2->rgt)->equals(2);
                expect($category2->level)->equals(1);

                $category3 = new CategoriesManyRoots();
                $category3->name = 'Computers';
                $category3->saveNode();

                $I->seeInDatabase(CategoriesManyRoots::$table, ['name' => 'Computers']);

                $category3 = CategoriesManyRoots::findFirst(3);

                expect($category3->root)->equals(3);
                expect($category3->lft)->equals(1);
                expect($category3->rgt)->equals(2);
                expect($category3->level)->equals(1);

                $I->seeNumRecords(3, CategoriesManyRoots::$table);

                $this->checkIntegrity($category1->root);
                $this->checkIntegrity($category2->root);
                $this->checkIntegrity($category3->root);
            }
        );
    }

    /**
     * Creating more than one root by using one tree per table
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-02-28
     */
    public function testShouldCatchExceptionWhenCreateARootNodeUsingOneTreePerTable()
    {
        $this->specify(
            'Test managed to create more than one root by using one tree per table',
            function () {
                $category = new CategoriesOneRoot();
                $category->name = 'Mobile Phones';
                $category->saveNode();

                $category = new CategoriesOneRoot();
                $category->name = 'Computers';
                $category->saveNode();
            }, [
                'throws' => [
                    'Phalcon\Mvc\Model\Exception',
                    'Cannot create more than one root in single root mode.'
                ]
            ]
        );
    }

    /**
     * Getting all roots
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-02-28
     */
    public function testShouldDetectRoots()
    {
        $this->specify(
            "Model can't determine roots correctly",
            function () {
                expect((new CategoriesManyRoots)->roots())->count(0);

                $category1 = new CategoriesManyRoots();
                $category1->name = 'Mobile Phones';
                $category1->saveNode();

                expect($category1->roots())->count(1);

                $category2 = new CategoriesManyRoots();
                $category2->name = 'Cars';
                $category2->saveNode();

                expect($category2->roots())->count(2);
                expect($category2->roots())->isInstanceOf('Phalcon\Mvc\Model\Resultset\Simple');

                $this->checkIntegrity($category1->root);
                $this->checkIntegrity($category2->root);
            }
        );
    }

    /**
     * Add nodes to the tree
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-02-28
     */
    public function testShouldAddChildNodes()
    {
        $this->specify(
            'Unable to add nodes to the tree correctly',
            function () {
                $cars = new CategoriesManyRoots();
                $cars->name = 'Cars';
                $cars->saveNode();

                $ford = new CategoriesManyRoots();
                $ford->name = 'Ford';

                $mercedes = new CategoriesManyRoots();
                $mercedes->name = 'Mercedes';

                $audi = new CategoriesManyRoots();
                $audi->name = 'Audi';

                $ford->appendTo($cars);
                $mercedes->insertAfter($ford);
                $audi->insertBefore($ford);

                $phones = new CategoriesManyRoots();
                $phones->name = 'Mobile Phones';
                $phones->saveNode();

                $expected = [
                    'Cars',
                    '     Audi',
                    '     Ford',
                    '     Mercedes',
                    'Mobile Phones',
                ];

                expect($this->prettifyRoots())->equals($expected);

                $this->checkIntegrity($cars->root);
                $this->checkIntegrity($phones->root);
            }
        );
    }

    /**
     * Created nodes in the desired place
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-03-01
     * @issue  513
     */
    public function testShouldAddBelowAndAbove()
    {
        $this->specify(
            'Unable to created nodes in the desired place',
            function () {
                $root = new CategoriesManyRoots;
                $root->name = 'ROOT';
                $root->saveNode();

                $node1 = new CategoriesManyRoots;
                $node1->name = 'A';
                $node1->appendTo($root);

                $node2 = new CategoriesManyRoots;
                $node2->name = 'B';
                $node2->appendTo($root);

                $node3 = new CategoriesManyRoots;
                $node3->name = 'C';
                $node3->prependTo($root);

                $expected = [
                    'ROOT',
                    '     C',
                    '     A',
                    '     B',
                ];

                expect($this->prettifyRoots())->equals($expected);
                $this->checkIntegrity($root->root);
            }
        );
    }

    /**
     * Move node as first
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-02-28
     * @issue  534
     */
    public function testShouldMoveNodeAsFirst()
    {
        $this->specify(
            'Unable to move nodes correctly by using moveAsFirst',
            function () {
                $this->createTree();

                $mercedes = CategoriesManyRoots::findFirst(3);
                $samsung = CategoriesManyRoots::findFirst(6);

                $x100 = new CategoriesManyRoots();
                $x100->name = 'X100';
                $x100->appendTo($mercedes);

                $c200 = new CategoriesManyRoots();
                $c200->name = 'C200';
                $c200->prependTo($mercedes);

                $expected = [
                    'Cars',
                    '     Audi',
                    '     Ford',
                    '     Mercedes',
                    '          C200',
                    '          X100',
                    'Mobile Phones',
                    '     iPhone',
                    '     Samsung',
                    '     Motorola',
                ];

                expect($this->prettifyRoots())->equals($expected);

                $c200->moveAsFirst($samsung);
                $x100->moveAsFirst($samsung);

                $expected = [
                    'Cars',
                    '     Audi',
                    '     Ford',
                    '     Mercedes',
                    'Mobile Phones',
                    '     iPhone',
                    '     Samsung',
                    '          X100',
                    '          C200',
                    '     Motorola',
                ];

                expect($this->prettifyRoots())->equals($expected);

                $this->checkIntegrity(CategoriesManyRoots::findFirst(1)->root); // cars
                $this->checkIntegrity(CategoriesManyRoots::findFirst(5)->root); // phones
            }
        );
    }
}
