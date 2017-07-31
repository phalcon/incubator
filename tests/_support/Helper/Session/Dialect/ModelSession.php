<?php

namespace Helper\Session\Dialect;

/**
 * Helper\Session\Dialect\ModelSession
 *
 * @copyright (c) 2011-2017 Phalcon Team
 * @link      https://phalconphp.com
 * @author    Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
 * @package   Helper\Session\Dialect
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */

use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;

class ModelSession extends Model
{
    public static $table = 'sessions';
}
