<?php
namespace Phalcon\Validation\Validator\Db;

use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;
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
 *     ),
 *     $di->get('db');
 * );
 * </code>
 *
 * If second parameter will be null (omitted) than validator will try to get database
 * connection from default DI instance with \Phalcon\Di::getDefault()->get('db');
 */

class Uniqueness extends Validator implements ValidatorInterface
{
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

        if (false === $this->isSetOption('table')) {
            throw new ValidationException('Validator require table option to be set');
        }

        if (false === $this->isSetOption('column')) {
            throw new ValidationException('Validator require column option to be set');
        }

        $this->db = $db;
    }

    /**
     * Executes the uniqueness validation
     *
     * @param  \Phalcon\Validation $validator
     * @param  string              $attribute
     * @return boolean
     */
    public function validate(Validation $validator, $attribute)
    {
        $table = $this->getOption('table');
        $column = $this->getOption('column');

        $result = $this->db->fetchOne(
            sprintf('SELECT COUNT(*) as count FROM %s WHERE %s = ?', $table, $column),
            Db::FETCH_ASSOC,
            array($validator->getValue($attribute))
        );

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
