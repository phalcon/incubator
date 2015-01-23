<?php
namespace Phalcon\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Mvc\CollectionInterface;

/**
 * Phalcon\Mvc\Model\Validator\IP
 *
 * Validates that a value is ipv4 address in valid range
 *
 *<code>
 *use Phalcon\Mvc\Model\Validator\CardNumber;
 *
 *class Data extends Phalcon\Mvc\Model
 *{
 *
 *  public function validation()
 *  {
 *      // Any pubic IP
 *      $this->validate(new IPv4(array(
 *          'field'             => 'server_ip',
 *          'version'           => IP::VERSION_4 | IP::VERSION_6, // v6 and v4. The same if not specified
 *          'allowReserved'     => false,   // False if not specified. Ignored for v6
 *          'allowPrivate'      => false,   // False if not specified
 *          'message'           => 'IP address has to be correct'
 *      )));
 *
 *      // Any public v4 address
 *      $this->validate(new IP(array(
 *          'field'             => 'ip_4',
 *          'version'           => IP::VERSION_4,
 *          'message'           => 'IP address has to be correct'
 *      )));
 *
 *      // Any v6 address
 *      $this->validate(new IP(array(
 *          'field'             => 'ip6',
 *          'version'           => IP::VERSION_6,
 *          'allowPrivate'      => true,
 *          'message'           => 'IP address has to be correct'
 *      )));
 *
 *      if ($this->validationHasFailed() == true) {
 *          return false;
 *      }
 *  }
 *
 *}
 *</code>
 */
class IP extends \Phalcon\Mvc\Model\Validator
{
    const VERSION_4  = FILTER_FLAG_IPV4;
    const VERSION_6  = FILTER_FLAG_IPV6;

    public function validate($record)
    {
        if (false === is_object($record)) {
            throw new Exception('Invalid parameter type.');
        }

        if (false === ($record instanceof ModelInterface || $record instanceof CollectionInterface)) {
            throw new Exception('Invalid parameter type.');
        }

        $field = $this->getOption('field');

        if (false === is_string($field)) {
            throw new Exception('Field name must be a string');
        }

        $value = $record->readAttribute($field);
        $version = $this->getOption('version') ?: FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
        $allowPrivate = $this->getOption('allowPrivate') ? 0 : FILTER_FLAG_NO_PRIV_RANGE;
        $allowReserved = $this->getOption('allowReserved') ? 0 : FILTER_FLAG_NO_RES_RANGE;

        $options = array(
            'options' => array(
                'default' => false,
            ),
            'flags' => $version | $allowPrivate | $allowReserved,
        );

        $result = filter_var($value, FILTER_VALIDATE_IP, $options);

        if (false === $result) {
            $message = $this->getOption('message') ?: 'IP address is incorrect';
            $this->appendMessage($message, $field, "IP");
        }

        return (boolean) $result;
    }
}
