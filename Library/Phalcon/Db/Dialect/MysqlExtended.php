<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
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

namespace Phalcon\Db\Dialect;

use Phalcon\Db\Exception;

/**
 * Phalcon\Db\Dialect\MysqlExtended
 *
 * Generates database specific SQL for the MySQL RDBMS. Extended version.
 *
 * @package Phalcon\Db\Dialect
 */
class MysqlExtended extends Mysql
{
    /**
     * Transforms an intermediate representation for a expression into a database system valid expression.
     *
     * @param array $expression
     * @param string $escapeChar
     * @param mixed $bindCounts
     * @return string
     *
     * @throws Exception
     */
    public function getSqlExpression(array $expression, $escapeChar = null, $bindCounts = null)
    {
        if ($expression["type"] == 'functionCall') {
            switch (strtoupper($expression["name"])) {
                case 'DATE_INTERVAL':
                    if (count($expression["arguments"]) != 2) {
                        throw new Exception('DATE_INTERVAL requires 2 parameters');
                    }

                    switch ($expression["arguments"][1]['value']) {
                        case "'DAY'":
                            return 'INTERVAL ' . $this->getSqlExpression($expression["arguments"][0]) . ' DAY';
                        case "'MONTH'":
                            return 'INTERVAL ' . $this->getSqlExpression($expression["arguments"][0]) . ' MONTH';
                        case "'YEAR'":
                            return 'INTERVAL ' . $this->getSqlExpression($expression["arguments"][0]) . ' YEAR';
                    }
                    break;

                case 'FULLTEXT_MATCH':
                    if (count($expression["arguments"]) < 2) {
                        throw new Exception('FULLTEXT_MATCH requires 2 parameters');
                    }

                    $arguments = [];
                    $length = count($expression["arguments"]) - 1;
                    for ($i = 0; $i < $length; $i++) {
                        $arguments[] = $this->getSqlExpression($expression["arguments"][$i]);
                    }

                    return 'MATCH(' . join(', ', $arguments) . ') AGAINST (' .
                    $this->getSqlExpression($expression["arguments"][$length]) . ')';
                    break;

                case 'FULLTEXT_MATCH_BMODE':
                    if (count($expression["arguments"]) < 2) {
                        throw new Exception('FULLTEXT_MATCH requires 2 parameters');
                    }

                    $arguments = [];
                    $length = count($expression["arguments"]) - 1;
                    for ($i = 0; $i < $length; $i++) {
                        $arguments[] = $this->getSqlExpression($expression["arguments"][$i]);
                    }

                    return 'MATCH(' . join(', ', $arguments) . ') AGAINST (' .
                    $this->getSqlExpression($expression["arguments"][$length]) . ' IN BOOLEAN MODE)';
                    break;

                case 'REGEXP':
                    if (count($expression['arguments']) != 2) {
                        throw new Exception('REGEXP requires 2 parameters');
                    }

                    return $this->getSqlExpression($expression['arguments'][0]) .
                    ' REGEXP (' . $this->getSqlExpression($expression['arguments'][1]) . ')';
                    break;
            }
        }

        return parent::getSqlExpression($expression, $escapeChar, $bindCounts);
    }
}
