<?php
namespace Phalcon\Utils;

/**
 * Class TruncateText
 *
 * @package Phalcon\Utils
 */
class TruncateText
{
    /**
     * Truncates text to given char limit.
     *
     * @param string $text  text which should be truncated
     * @param int    $limit char limit
     * @param string $break on which character should truncate happen
     * @param string $pad   padding string, will be appended on end of truncated string
     *
     * @return string truncated text
     */
    public static function truncateText($text, $limit, $break = ".", $pad = "...")
    {
        // Original PHP code by Chirp Internet: www.chirp.com.au
        // Please acknowledge use of this code by including this header

        $text = strip_tags($text);
        // return with no change if string is shorter than $limit
        if (strlen($text) <= $limit) {
            return $text;
        }
        // is $break present between $limit and the end of the string?
        if (false !== ($breakpoint = strpos($text, $break, $limit))) {
            if ($breakpoint < strlen($text) - 1) {
                $text = substr($text, 0, $breakpoint) . $pad;

            }

        }

        return trim($text);

    }
}
