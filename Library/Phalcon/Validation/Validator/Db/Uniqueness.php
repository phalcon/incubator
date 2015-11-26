<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2015 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Tomasz Ślązok <tomek@sabaki.pl>                               |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Validation\Validator\Db;

use Phalcon\Validation\Validator;
use Phalcon\Validation\Message;
use Phalcon\Db\Adapter\Pdo as DbConnection;
use Phalcon\Validation\Exception as ValidationException;
use Phalcon\DiInterface;
use Phalcon\Di;
use Phalcon\Db;
use Phalcon\Validation;

/**
 * Phalcon\Validation\Validator\Db\Uniqueness
 *
 * Validator for checking uniqueness of field in database
 *
 * <code>
 * $uniqueness = new Uniqueness(
 *     array(
 *         'table' => 'users',
 *         'column' => 'login',
 *         'message' => 'already taken',
 *          'exclude' => array(
 *              'column' => 'id',
 *              'value' => 1 //same ID to exclude
 *          )
 *     ),
 *     $di->get('db');
 * );
 * </code>
 *
 * Exclude option is Optional. Ideal to validate existins records
 *
 * If second parameter will be null (omitted) than validator will try to get database
 * connection from default DI instance with \Phalcon\Di::getDefault()->get('db');
 */

class Uniqueness extends Validator implements ValidatorInterface {

    /**
     * Database connection
     * @var \Phalcon\Db\Adapter\Pdo
     */
    private $db;

    /**
     * Class constructor.
     *
     * @param  array               $options
     * @param  DbConnection        $db
     * @throws ValidationException
     */
    public function __construct(array $options = array(), $db = null)
    {
        parent::__construct($options);

        if (null === $db) {
            // try to get db instance from default Dependency Injection
            $di = Di::getDefault();

            if ($di instanceof DiInterface && $di->has('db')) {
                $db = $di->get('db');
            }
        }

        if (!($db instanceof DbConnection)) {
            throw new ValidationException('Validator Uniquness require connection to database');
        }

        if (false === $this->hasOption('table')) {
            throw new ValidationException('Validator require table option to be set');
        }

        if (false === $this->hasOption('column')) {
            throw new ValidationException('Validator require column option to be set');
        }

        if ($this->hasOption('exclude')) {

            if (empty($this->getOption('exclude')['column'])) {
                throw new ValidationException('Validator with exclude option, require column option to be set');
            }

            if (empty($this->getOption('exclude')['value'])) {
                throw new ValidationException('Validator with exclude option, require value option to be set');
            }

        }

        $this->setDb($db);
    }

    /**
     * @return DbConnection
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param DbConnection $db
     * @return Uniqueness
     */
    public function setDb(DbConnection $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Executes the uniqueness validation
     *
     * @param  \Phalcon\Validation $validator
     * @param  string $attribute
     * @return boolean
     */
    public function validate(Validation $validator, $attribute)
    {
        $table = $this->getOption('table');
        $column = $this->getOption('column');

        if($this->hasOption('exclude')) {
            $result = $this->getDb()->fetchOne(
                sprintf('SELECT COUNT(*) as count FROM %s WHERE %s = ? and %s != ?', $table, $column, $this->getOption('exclude')['column']),
                Db::FETCH_ASSOC,
                array($validator->getValue($attribute), $this->getOption('exclude')['value'])
            );
        } else {
            $result = $this->getDb()->fetchOne(
                sprintf('SELECT COUNT(*) as count FROM %s WHERE %s = ?', $table, $column),
                Db::FETCH_ASSOC,
                array($validator->getValue($attribute))
            );
        }


        if ($result['count']) {
            $message = $this->getOption('message');

            if (null === $message) {
                $message = 'Already taken. Choose another!';
            }

            $validator->appendMessage(new Message($message, $attribute, 'Uniqueness'));

            return false;
        }

        return true;
    }

}
