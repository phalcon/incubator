<?php

namespace Phalcon\Cache\Frontend;

use Phalcon\Cache\Frontend\Data;

/**
 * Class Msgpack
 * @package Phalcon\Cache\Frontend
 *
 * @author Yoshihiro Misawa
 */
class Msgpack extends Data
{

    /**
     * {@inheritdoc}
     *
     * @param  mixed  $data
     * @return string
     */
    public function beforeStore($data)
    {
        return msgpack_pack($data);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $data
     * @return mixed
     */
    public function afterRetrieve($data)
    {
        return msgpack_unpack($data);
    }
}
