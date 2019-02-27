<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Db\Dialect;

use Phalcon\Text;
use Phalcon\Db\Column;
use Phalcon\Db\Dialect;
use Phalcon\Db\Exception;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\ReferenceInterface;

/**
 * Phalcon\Db\Dialect\Oracle
 *
 * Generates database specific SQL for the Oracle RDBMS.
 *
 * <code>
 * use Phalcon\Db\Adapter\Pdo\Oracle;
 * use Phalcon\Db\Adapter\Pdo\Oracle as Connection;
 *
 * $connection = new Connection([
 *     'dbname'       => '//localhost/enigma',
 *     'username'     => 'oracle',
 *     'password'     => 'secret',
 *     'dialectClass' => Oracle::class,
 * ]);
 * </code>
 *
 * @package Phalcon\Db\Dialect
 */
class Oracle extends Dialect
{
    // @codingStandardsIgnoreStart
    protected $_escapeChar = "";
    // @codingStandardsIgnoreEnd

    /**
     * Returns a SQL modified with a LOCK IN SHARE MODE clause
     *
     * @param string $sqlQuery
     * @return string
     */
    public function sharedLock($sqlQuery) {
        return $sqlQuery . ' LOCK IN SHARE MODE';
    }

    /**
     * Generates the SQL for LIMIT clause.
     *
     * @param string $sqlQuery
     * @param mixed $number
     * @return string
     */
    public function limit($sqlQuery, $number)
    {
        $offset = 0;

        if (is_array($number)) {
            $offset = $number[1] !=null? $number[1]:0;
            $limit = $number[0];
        } else {
            $limit = $number;
        }

        $sqlQuery = sprintf(
            /** @lang text */
            'SELECT * FROM (SELECT Z1.*, ROWNUM PHALCON_RN FROM (%s) Z1',
            $sqlQuery
        );

        $sqlQuery .= sprintf(' WHERE ROWNUM <= %s', $limit);

        $sqlQuery .= ')';

        $sqlQuery .= sprintf(' WHERE PHALCON_RN >= %s', $offset);
        return $sqlQuery;
    }

    /**
     * Gets the column name in Oracle.
     *
     * @param ColumnInterface $column
     * @return string
     *
     * @throws Exception
     */
    public function getColumnDefinition(ColumnInterface $column)
    {
        $type = $column->getType();
        $size = $column->getSize();

        switch ($type) {
            case Column::TYPE_INTEGER:
                $columnSql = 'INTEGER';
                break;
            case Column::TYPE_DATE:
                $columnSql = 'DATE';
                break;
            case Column::TYPE_VARCHAR:
                $columnSql = 'VARCHAR2(' . $size . ')';
                break;
            case Column::TYPE_DECIMAL:
                $scale = $column->getScale();
                $columnSql = 'NUMBER(' . $size . ',' . $scale . ')';
                break;
            case Column::TYPE_DATETIME:
                $columnSql = 'TIMESTAMP';
                break;
            case Column::TYPE_TIMESTAMP:
                $columnSql = 'TIMESTAMP';
                break;
            case Column::TYPE_CHAR:
                $columnSql = 'CHAR(' . $size . ')';
                break;
            case Column::TYPE_TEXT:
                $columnSql = 'TEXT';
                break;
            case Column::TYPE_FLOAT:
                $scale = $column->getScale();
                $columnSql = 'FLOAT(' . $size . ',' . $scale . ')';
                break;
            case Column::TYPE_BOOLEAN:
                $columnSql = 'TINYINT(1)';
                break;
            default:
                throw new Exception('Unrecognized Oracle data type at column ' . $column->getName());
        }

        return $columnSql;
    }

    /**
     * Generates SQL to add a column to a table.
     *
     * @param string $tableName
     * @param string $schemaName
     * @param ColumnInterface $column
     * @return string
     *
     * @throws Exception
     */
    public function addColumn($tableName, $schemaName, ColumnInterface $column)
    {
        throw new Exception('Not implemented yet.');
    }

    /**
     * Generates SQL to modify a column in a table.
     *
     * @param string $tableName
     * @param string $schemaName
     * @param ColumnInterface $column
     * @param ColumnInterface|null $current
     * @return string
     *
     * @throws Exception
     */
    public function modifyColumn($tableName, $schemaName, ColumnInterface $column, ColumnInterface $current = null)
    {
        throw new Exception('Not implemented yet.');
    }

    /**
     * Generates SQL to delete a column from a table.
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $columnName
     * @return string
     *
     * @throws Exception
     */
    public function dropColumn($tableName, $schemaName, $columnName)
    {
        throw new Exception('Not implemented yet.');
    }

    /**
     * Generates SQL to add an index to a table.
     *
     * @param string $tableName
     * @param string $schemaName
     * @param IndexInterface $index
     * @return string
     *
     * @throws Exception
     */
    public function addIndex($tableName, $schemaName, IndexInterface $index)
    {
        throw new Exception('Not implemented yet.');
    }

    /**
     * Generates SQL to delete an index from a table.
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $indexName
     * @return string
     *
     * @throws Exception
     */
    public function dropIndex($tableName, $schemaName, $indexName)
    {
        throw new Exception('Not implemented yet.');
    }

    /**
     * Generates SQL to add the primary key to a table.
     *
     * @param string $tableName
     * @param string $schemaName
     * @param IndexInterface $index
     * @return string
     *
     * @throws Exception
     */
    public function addPrimaryKey($tableName, $schemaName, IndexInterface $index)
    {
        throw new Exception('Not implemented yet.');
    }

    /**
     * Generates SQL to delete primary key from a table.
     *
     * @param string $tableName
     * @param string $schemaName
     * @return string
     *
     * @throws Exception
     */
    public function dropPrimaryKey($tableName, $schemaName)
    {
        throw new Exception('Not implemented yet.');
    }

    /**
     * Generates SQL to add an index to a table.
     *
     * @param string $tableName
     * @param string $schemaName
     * @param ReferenceInterface $reference
     * @return string
     *
     * @throws Exception
     */
    public function addForeignKey($tableName, $schemaName, ReferenceInterface $reference)
    {
        throw new Exception('Not implemented yet.');
    }

    /**
     * Generates SQL to delete a foreign key from a table.
     *
     * @param string $tableName
     * @param string $schemaName
     * @param string $referenceName
     * @return string
     *
     * @throws Exception
     */
    public function dropForeignKey($tableName, $schemaName, $referenceName)
    {
        throw new Exception('Not implemented yet.');
    }

    /**
     * Generates SQL to create a table in Oracle.
     *
     * @param string $tableName
     * @param string $schemaName
     * @param array $definition
     * @return string
     *
     * @throws Exception
     */
    public function createTable($tableName, $schemaName, array $definition)
    {
        throw new Exception('Not implemented yet.');
    }

    /**
     * Generates SQL to drop a table.
     *
     * @param string $tableName
     * @param string $schemaName
     * @param bool $ifExists
     * @return string
     */
    public function dropTable($tableName, $schemaName, $ifExists = true)
    {
        $this->_escapeChar = '';

        $table = $this->prepareTable($tableName, $schemaName);
        $sql = sprintf(
            /** @lang text */
            'DROP TABLE %s',
            $table
        );

        if ($ifExists) {
            $sql = sprintf(
                "BEGIN EXECUTE IMMEDIATE '%s'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END",
                $sql
            );
        }

        $this->_escapeChar = "'";

        return $sql;
    }

    /**
     * List all tables in database.
     *
     * <code>
     * print_r($dialect->listTables('blog'));
     * </code>
     *
     * @param string $schemaName
     * @return string
     */
    public function listTables($schemaName = null)
    {
        $baseQuery = /** @lang text */
            "SELECT TABLE_NAME, OWNER FROM ALL_TABLES %s ORDER BY OWNER, TABLE_NAME";

        if (!empty($schemaName)) {
            $schemaName = $this->escapeSchema($schemaName);

            return sprintf($baseQuery . 'WHERE OWNER = %s', Text::upper($schemaName));
        }

        return sprintf($baseQuery, '');
    }

    /**
     * Generates SQL checking for the existence of a schema.table
     *
     * <code>
     * echo $dialect->tableExists('posts', 'blog');
     * echo $dialect->tableExists('posts');
     * </code>
     *
     * @param string $tableName
     * @param string $schemaName
     * @return string
     */
    public function tableExists($tableName, $schemaName = null)
    {

        $oldEscapeChar=$this->_escapeChar;
        $this->_escapeChar = "'";
        $tableName = $this->escape(Text::upper($tableName));
        $baseQuery = sprintf(
            /** @lang text */
            "SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END RET FROM ALL_TABLES WHERE TABLE_NAME = %s",
            $tableName
        );

        if (!empty($schemaName)) {
            $schemaName = $this->escapeSchema($schemaName);

            $baseQuery=sprintf("%s AND OWNER = %s", $baseQuery, Text::upper($schemaName));
        }
        $this->_escapeChar = $oldEscapeChar;

        return $baseQuery;
    }

    /**
     * Generates SQL to create a view.
     *
     * @param string $viewName
     * @param array $definition
     * @param string $schemaName
     * @return string
     *
     * @throws Exception
     */
    public function createView($viewName, array $definition, $schemaName = null)
    {
        if (!isset($definition['sql']) || empty($definition['sql'])) {
            throw new Exception("The index 'sql' is required in the definition array");
        }

        return 'CREATE VIEW ' . Text::upper($this->prepareTable($viewName, $schemaName)) . ' AS ' . $definition['sql'];
    }

    /**
     * Generates SQL to drop a view.
     *
     * @param string $viewName
     * @param string $schemaName
     * @param bool $ifExists
     * @return string
     */
    public function dropView($viewName, $schemaName = null, $ifExists = true)
    {
        $this->_escapeChar = '';

        $view = Text::upper($this->prepareTable($viewName, $schemaName));
        $sql = sprintf(
            /** @lang text */
            'DROP VIEW %s',
            $view
        );

        if ($ifExists) {
            $sql = sprintf(
                "BEGIN FOR i IN (SELECT NULL FROM ALL_VIEWS WHERE VIEW_NAME = '%s') " .
                "LOOP EXECUTE IMMEDIATE '%s'; END LOOP; END",
                $view,
                $sql
            );
        }

        return $sql;
    }

    /**
     * Generates SQL checking for the existence of a schema.view
     *
     * @param string $viewName
     * @param string $schemaName
     *
     * @return string
     */
    public function viewExists($viewName, $schemaName = null)
    {
        $view = $this->prepareTable($viewName, $schemaName);
        $baseSql = sprintf(
            /** @lang text */
            "SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END RET FROM ALL_VIEWS WHERE VIEW_NAME = %s",
            Text::upper($view)
        );

        if (!empty($schemaName)) {
            $schemaName = $this->escapeSchema($schemaName, $this->_escapeChar);

            $baseSql .= sprintf("AND OWNER = %s", Text::upper($schemaName));
        }

        return $baseSql;
    }

    /**
     * Generates the SQL to list all views of a schema or user.
     *
     * @param string $schemaName
     * @return string
     */
    public function listViews($schemaName = null)
    {
        $baseSql = /** @lang text */
            'SELECT VIEW_NAME FROM ALL_VIEWS';

        if (!empty($schemaName)) {
            $schemaName = $this->escapeSchema($schemaName);

            $baseSql .= sprintf(" WHERE OWNER = %s", Text::upper($schemaName));
        }

        return $baseSql . ' ORDER BY VIEW_NAME';
    }

    /**
     * Generates SQL to describe a table.
     *
     * @param string $table
     * @param string $schema
     * @return string
     */
    public function describeColumns($table, $schema = null)
    {
        $oldEscapeChar= $this->_escapeChar;
        $this->_escapeChar = "'";
        $table = $this->escape($table);
        $sql = 'SELECT TC.COLUMN_NAME, TC.DATA_TYPE, TC.DATA_LENGTH, TC.DATA_PRECISION, TC.DATA_SCALE, TC.NULLABLE, ' .
               'C.CONSTRAINT_TYPE, TC.DATA_DEFAULT, CC.POSITION FROM ALL_TAB_COLUMNS TC LEFT JOIN ' .
               '(ALL_CONS_COLUMNS CC JOIN ALL_CONSTRAINTS C ON (CC.CONSTRAINT_NAME = C.CONSTRAINT_NAME AND ' .
               "CC.TABLE_NAME = C.TABLE_NAME AND CC.OWNER = C.OWNER AND C.CONSTRAINT_TYPE = 'P')) ON " .
               'TC.TABLE_NAME = CC.TABLE_NAME AND TC.COLUMN_NAME = CC.COLUMN_NAME WHERE TC.TABLE_NAME = %s %s '.
               'ORDER BY TC.COLUMN_ID';


        if (!empty($schema)) {
            $schema = $this->escapeSchema($schema);

            $queryBase=sprintf($sql, Text::upper($table), 'AND TC.OWNER = ' . Text::upper($schema));
        } else {
            $queryBase=sprintf($sql, Text::upper($table), '');
        }
        $this->_escapeChar = $oldEscapeChar;
        return $queryBase;
    }

    /**
     * Generates SQL to query indexes on a table.
     *
     * @param string $table
     * @param string $schema
     * @return string
     */
    public function describeIndexes($table, $schema = null)
    {
        $table = $this->escape($table);
        $sql = 'SELECT I.TABLE_NAME, 0 AS C0, I.INDEX_NAME, IC.COLUMN_POSITION, IC.COLUMN_NAME ' .
               'FROM ALL_INDEXES I JOIN ALL_IND_COLUMNS IC ON I.INDEX_NAME = IC.INDEX_NAME WHERE  I.TABLE_NAME = ' .
               Text::upper($table);

        if (!empty($schema)) {
            $schema = $this->escapeSchema($schema);

            $sql .= ' AND IC.INDEX_OWNER = %s'. Text::upper($schema);
        }

        return $sql;
    }

    public function describeReferences($table, $schema = null)
    {
        $table = $this->escape($table);

        $sql = 'SELECT AC.TABLE_NAME, CC.COLUMN_NAME, AC.CONSTRAINT_NAME, AC.R_OWNER, RCC.TABLE_NAME R_TABLE_NAME, ' .
               'RCC.COLUMN_NAME R_COLUMN_NAME FROM ALL_CONSTRAINTS AC JOIN ALL_CONS_COLUMNS CC ' .
               'ON AC.CONSTRAINT_NAME = CC.CONSTRAINT_NAME JOIN ALL_CONS_COLUMNS RCC ON AC.R_OWNER = RCC.OWNER ' .
               "AND AC.R_CONSTRAINT_NAME = RCC.CONSTRAINT_NAME WHERE AC.CONSTRAINT_TYPE='R' ";

        if (!empty($schema)) {
            $schema = $this->escapeSchema($schema);
            $sql .= 'AND AC.OWNER = ' . Text::upper($schema) . ' AND AC.TABLE_NAME = ' . Text::upper($table);
        } else {
            $sql .= 'AND AC.TABLE_NAME = ' . Text::upper($table);
        }

        return $sql;
    }

    /**
     * Generates the SQL to describe the table creation options.
     *
     * @param string $table
     * @param string $schema
     * @return string
     */
    public function tableOptions($table, $schema = null)
    {
        return '';
    }

    /**
     * Checks whether the platform supports savepoints.
     *
     * @return bool
     */
    public function supportsSavepoints()
    {
        return false;
    }

    /**
     * Checks whether the platform supports releasing savepoints.
     *
     * @return bool
     */
    public function supportsReleaseSavepoints()
    {
        return false;
    }

    /**
     * Prepares table for this RDBMS.
     *
     * @param string $table
     * @param string $schema
     * @param string $alias
     * @param string $escapeChar
     * @return string
     */
    protected function prepareTable($table, $schema = null, $alias = null, $escapeChar = null)
    {
        $table = $this->escape($table, $escapeChar);

        // Schema
        if (!empty($schema)) {
            $table = $this->escapeSchema($schema, $escapeChar) . '.' . $table;
        }

        // Alias
        if (!empty($alias)) {
            $table .= ' ' . $this->escape($alias, $escapeChar);
        }

        return $table;
    }
}
