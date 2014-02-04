<?php
namespace Phalcon\Utils;

/**
 * Class Id2Dir
 *
 * @see http://stackoverflow.com/a/3356859
 *
 * @package Phalcon\Utils
 */
class Id2Dir
{
    /**
     * Converts unique integer identifier (id/primary key) to directory location,
     * useful for large number of images and/or user profiles.
     *
     * @param int $uniqueId unique identifier
     *
     * @return string path to folder, without leading slash
     */
    public static function id2Dir($uniqueId)
    {
        $level1 = ($uniqueId / 100000000) % 100000000;
        $level2 = (($uniqueId - $level1 * 100000000) / 100000) % 100000;
        $level3 = (($uniqueId - ($level1 * 100000000) - ($level2 * 100000)) / 100) % 1000;
        $file   = $uniqueId - (($level1 * 100000000) + ($level2 * 100000) + ($level3 * 100));

        return sprintf("%03d", $level1)
        . '/' . sprintf("%03d", $level2)
        . '/' . sprintf("%03d", $level3)
        . '/' . $file;
    }
}
