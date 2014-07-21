<?php

namespace Phalcon\Db\Adapter\Pdo;

/**
 * Phalcon\Db\Adapter\Pdo\MysqlExt
 *
 * Concept of adapter with extended insert/update syntax and fetchField method
 *
 *<code>
 *
 *    $config = array(
 *        "host" => "192.168.0.11",
 *        "dbname" => "blog",
 *        "port" => 3306,
 *        "username" => "sigma",
 *        "password" => "secret"
 *    );
 *
 *    $connection = new Phalcon\Db\Adapter\Pdo\MysqlExt($config);
 *
 *</code>
 */
class MysqlExt extends \Phalcon\Db\Adapter\Pdo\Mysql implements \Phalcon\Events\EventsAwareInterface, \Phalcon\Db\AdapterInterface
{

    /**
     * Returns the first field if first row in a SQL query result
     *
     *<code>
     *    //Getting count of robots
     *    $robotsCount = $connection->fetchField("SELECT count(*) FROM robots");
     *    print_r($robotsCount);
     *
     *    //Getting id of first robot
     *    $robot = $connection->fetchField("SELECT id FROM robots");
     *    print_r($robot);
     *</code>
     *
     * @param string $sqlQuery
     * @param array $placeholders
     * @return mixed
     */
    public function fetchField($sqlQuery, $placeholders = null)
    {
        $row = $this->fetchOne($sqlQuery, \Phalcon\Db::FETCH_NUM, $placeholders);
        if ($row && isset($row[0])) {
            return $row[0];
        } else {
            return null;
        }
    }


    /**
     * Inserts data into a table using custom RBDM SQL syntax
     * Another, more convenient syntax
     *
     * <code>
     * //Inserting a new robot
     * $success = $connection->insert(
     *     "robots",
     *     array(
     *          "name" => "Astro Boy",
     *          "year" => 1952
     *      )
     * );
     *
     * //Next SQL sentence is sent to the database system
     * INSERT INTO `robots` (`name`, `year`) VALUES ("Astro boy", 1952);
     * </code>
     *
     * @param    string $table
     * @param    array $data
     * @param    array $dataTypes
     * @return    boolean
     */
    public function insertAsDict($table, $data, $dataTypes = null)
    {
        if (empty($data)) {
            return false;
        }

        $values = $fields = array();
        foreach ($data as $field => $value) {
            $fields[] = $field;
            $values[] = $value;
        }

        return $this->insert($table, $values, $fields, $dataTypes);
    }


    /**
     * Updates data on a table using custom RBDM SQL syntax
     * Another, more convenient syntax
     *
     * <code>
     * //Updating existing robot
     * $success = $connection->update(
     *     "robots",
     *     array(
     *          "name" => "New Astro Boy"
     *      ),
     *     "id = 101"
     * );
     *
     * //Next SQL sentence is sent to the database system
     * UPDATE `robots` SET `name` = "Astro boy" WHERE id = 101
     * </code>
     *
     * @param    string $table
     * @param    array $data
     * @param    string $whereCondition
     * @param    array $dataTypes
     * @return    boolean
     */
    public function updateAsDict($table, $data, $whereCondition = null, $dataTypes = null)
    {
        if (empty($data)) {
            return false;
        }

        $values = $fields = array();
        foreach ($data as $field => $value) {
            $fields[] = $field;
            $values[] = $value;
        }

        return $this->update($table, $fields, $values, $whereCondition, $dataTypes);
    }
}
