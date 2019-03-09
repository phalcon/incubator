<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalconphp.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Test\Unit\Translate\Adapter;

use UnitTester;
use Phalcon\Di;
use Phalcon\Mvc\Model\Metadata\Memory;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Translate\Adapter\Database;

class DatabaseCest
{
    /**
     * @var DiInterface
     */
    private $previousDependencyInjector;
    
    /**
     * @var Array
     */
    private $config = null;

    /**
     * executed before each test
     */
    public function _before(UnitTester $I)
    {
        $this->previousDependencyInjector = Di::getDefault();

        $di = new Di();

        $di->setShared('modelsMetadata', new Memory());
        $di->setShared('modelsManager', new Manager());
        $di->setShared('db', function () {
            return new Mysql([
                'host'     => env('TEST_DB_HOST', '127.0.0.1'),
                'username' => env('TEST_DB_USER', 'incubator'),
                'password' => env('TEST_DB_PASSWD', 'secret'),
                'dbname'   => env('TEST_DB_NAME', 'incubator'),
                'charset'  => env('TEST_DB_CHARSET', 'utf8'),
                'port'     => env('TEST_DB_PORT', 3306),
            ]);
        });
        
        if ($this->previousDependencyInjector instanceof DiInterface) {
            Di::setDefault($di);
        }
        
        $this->config = [
            'en' => [
                'db'                     => $di->get('db'), // Here we're getting the database from DI
                'table'                  => 'translations', // The table that is storing the translations
                'language'               =>  'en_US' // Now we're getting the best language for the user];
            ],
            'fr' => [
                'db'                     => $di->get('db'), // Here we're getting the database from DI
                'table'                  => 'translations', // The table that is storing the translations
                'language'               =>  'fr_FR' // Now we're getting the best language for the user];
            ],
            'es' => [
                'db'                     => $di->get('db'), // Here we're getting the database from DI
                'table'                  => 'translations', // The table that is storing the translations
                'language'               =>  'es_ES' // Now we're getting the best language for the user];
            ]
        ];
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

    public function testEnTranslate(UnitTester $I)
    {
        $params     = $this->config['en'];
        $translator = new Database($params);

        $expect = 'Hello!';
        $actual = $translator->query('hello');
        $I->assertEquals($expect, $actual);
    }
    
    public function testFrTranslate(UnitTester $I)
    {
        $params     = $this->config['fr'];
        $translator = new Database($params);

        $expect = 'Salut!';
        $actual = $translator->query('hello');
        $I->assertEquals($expect, $actual);
    }
    
    public function testEsTranslate(UnitTester $I)
    {
        $params     = $this->config['es'];
        $translator = new Database($params);

        $expect = '¡Hola!';
        $actual = $translator->query('hello');
        $I->assertEquals($expect, $actual);
    }
}
