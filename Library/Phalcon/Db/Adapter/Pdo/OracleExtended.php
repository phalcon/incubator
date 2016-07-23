<?php

/*
 +------------------------------------------------------------------------+
 | Phalcon Framework                                                      |
 +------------------------------------------------------------------------+
 | Copyright (c) 2011-2016 Phalcon Team (https://phalconphp.com)          |
 +------------------------------------------------------------------------+
 | This source file is subject to the New BSD License that is bundled     |
 | with this package in the file docs/LICENSE.txt.                        |
 |                                                                        |
 | If you did not receive a copy of the license and are unable to         |
 | obtain it through the world-wide-web, please send an email             |
 | to license@phalconphp.com so we can send you a copy immediately.       |
 +------------------------------------------------------------------------+
 | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
 |          Eduar Carvajal <eduar@phalconphp.com>                         |
 +------------------------------------------------------------------------+
 */

namespace Phalcon\Db\Adapter\Pdo;

use Phalcon\Db;
use Phalcon\Db\Column;
use Phalcon\Db\RawValue;
use Phalcon\Db\ColumnInterface;

/**
 * Phalcon\Db\Adapter\Pdo\Oracle
 *
 * Specific functions for the Oracle database system.
 *
 * <code>
 * use Phalcon\Db\Adapter\Pdo\Oracle;
 *
 * $connection = new Oracle([
 *     'dbname'   => '//localhost/dbname',
 *     'username' => 'oracle',
 *     'password' => 'oracle',
 * ]);
 * </code>
 *
 * @property \Phalcon\Db\Dialect\Oracle _dialect
 * @package Phalcon\Db\Adapter\Pdo
 * @todo Must me renamed to 'class Oracle extends PdoAdapter implements AdapterInterface'
 * @todo after removing Oracle from Phalcon
 */
class OracleExtended extends Oracle
{
    // @codingStandardsIgnoreStart
    protected $_type = 'oci';
    protected $_dialectType = 'oracle';
    // @codingStandardsIgnoreEnd

    /**
     * This method is automatically called in \Phalcon\Db\Adapter\Pdo constructor.
     * Call it when you need to restore a database connection.
     *
     * @param array $descriptor
     * @return bool
     */
    public function connect(array $descriptor = null)
    {
        if (empty($descriptor)) {
            $descriptor = $this->_descriptor;
        }

        $status = parent::connect($descriptor);

        if (isset($descriptor['startup']) && $descriptor['startup']) {
            $startup = $descriptor['startup'];
            if (!is_array($startup)) {
                $startup = [$startup];
            }

            foreach ($startup as $value) {
                $this->execute($value);
            }
        }

        return $status;
    }

    /**
     * Returns an array of \Phalcon\Db\Column objects describing a table.
     *
     * <code>
     * var_dump($connection->describeColumns('posts'));
     * </code>
     *
     * @param string $table
     * @param string $schema
     * @return ColumnInterface[]
     */
    public function describeColumns($table, $schema = null)
    {
        $columns = [];
        $oldColumn = null;

        /**
         * 0:column_name,
         * 1:data_type,
         * 2:data_length,
         * 3:data_precision,
         * 4:data_scale,
         * 5:nullable,
         * 6:constraint_type,
         * 7:default,
         * 8:position;
         */
        $sql = $this->_dialect->describeColumns($table, $schema);
        foreach ($this->fetchAll($sql, Db::FETCH_NUM) as $field) {
            $definition      = ['bindType' => 2];
            $columnSize      = $field[2];
            $columnPrecision = $field[3];
            $columnScale     = $field[4];
            $columnType      = $field[1];

            /**
             * Check the column type to get the correct Phalcon type
             */
            while (true) {
                if (false !== strpos($columnType, 'NUMBER')) {
                    $definition['type']      = Column::TYPE_DECIMAL;
                    $definition['isNumeric'] = true;
                    $definition['size']      = $columnPrecision;
                    $definition['scale']     = $columnScale;
                    $definition['bindType']  = 32;
                    break;
                }

                if (false !== strpos($columnType, 'INTEGER')) {
                    $definition['type']      = Column::TYPE_INTEGER;
                    $definition['isNumeric'] = true;
                    $definition['size']      = $columnPrecision;
                    $definition['bindType']  = 1;
                    break;
                }

                if (false !== strpos($columnType, 'VARCHAR2')) {
                    $definition['type']      = Column::TYPE_VARCHAR;
                    $definition['size']      = $columnSize;
                    break;
                }

                if (false !== strpos($columnType, 'FLOAT')) {
                    $definition['type']      = Column::TYPE_FLOAT;
                    $definition['isNumeric'] = true;
                    $definition['size']      = $columnSize;
                    $definition['scale']     = $columnScale;
                    $definition['bindType']  = 32;
                    break;
                }

                if (false !== strpos($columnType, 'TIMESTAMP')) {
                    $definition['type'] = Column::TYPE_TIMESTAMP;
                    break;
                }

                if (false !== strpos($columnType, 'DATE')) {
                    $definition['type'] = Column::TYPE_DATE;
                    break;
                }

                if (false !== strpos($columnType, 'RAW')) {
                    $definition['type'] = Column::TYPE_TEXT;
                    break;
                }

                if (false !== strpos($columnType, 'BLOB')) {
                    $definition['type'] = Column::TYPE_TEXT;
                    break;
                }

                if (false !== strpos($columnType, 'CLOB')) {
                    $definition['type'] = Column::TYPE_TEXT;
                    break;
                }

                if (false !== strpos($columnType, 'CHAR')) {
                    $definition['type'] = Column::TYPE_CHAR;
                    $definition['size'] = $columnSize;
                    break;
                }

                $definition['type'] = Column::TYPE_TEXT;
                break;
            }

            if (null === $oldColumn) {
                $definition['first'] = true;
            } else {
                $definition['after'] = $oldColumn;
            }

            /**
             * Check if the field is primary key
             */
            if ('P' == $field[6]) {
                $definition['primary'] = true;
            }

            /**
             * Check if the column allows null values
             */
            if ('N' == $field[5]) {
                $definition['notNull'] = true;
            }

            $columns[] = new Column($field[0], $definition);
            $oldColumn = $field[0];
        }

        return $columns;
    }

    /**
     * Returns the insert id for the auto_increment/serial column inserted in the latest executed SQL statement.
     *
     * <code>
     * // Inserting a new robot
     * $success = $connection->insert(
     *     'robots',
     *     ['Astro Boy', 1952],
     *     ['name', 'year'],
     * );
     *
     * // Getting the generated id
     * $id = $connection->lastInsertId();
     * <code>
     *
     * @param string $sequenceName
     * @return int
     */
    public function lastInsertId($sequenceName = null)
    {
        $sequenceName = $sequenceName ?: 'id';

        return $this->fetchAll('SELECT ' . $sequenceName . '.CURRVAL FROM dual', Db::FETCH_NUM)[0][0];
    }

    /**
     * Check whether the database system requires an explicit value for identity columns;
     *
     * @return bool
     */
    public function useExplicitIdValue()
    {
        return false;
    }

    /**
     * Return the default identity value to insert in an identity column.
     *
     * @return RawValue
     */
    public function getDefaultIdValue()
    {
        return new RawValue('default');
    }

    /**
     * Check whether the database system requires a sequence to produce auto-numeric values.
     *
     * @return bool
     */
    public function supportSequences()
    {
        return true;
    }
}
