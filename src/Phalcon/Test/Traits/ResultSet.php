<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Phoenix Osiris <phoenix@twistersfury.com>                     |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Test\Traits;

use Phalcon\Mvc\Model\Resultset as phResultset;
use Phalcon\Mvc\Model\ResultsetInterface;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use stdClass;

/**
 * Trait ResultSet. Adds Ability To Mock DB ResultSet (Without Actual Connection To DB)
 *
 * The resulting mock is only intended to mock the basic functions of a resultset.
 *
 * @package Phalcon\Test\Traits
 */
trait ResultSet
{
    /**
     * @param array $dataSet Mock Data Set To Use
     * @param string $className ResultSet Class To Mimic (Defaults To Abstract ResultSet)
     *
     * @return PHPUnit_Framework_MockObject_MockObject|phResultset|ResultsetInterface
     */
    public function mockResultSet(array $dataSet, $className = phResultset::class)
    {
        /** @var PHPUnit_Framework_TestCase $this */
        /** @var PHPUnit_Framework_MockObject_MockObject $mockResultSet */


        $mockResultSet = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'valid',
                    'current',
                    'key',
                    'next',
                    'toArray',
                    'getFirst',
                    'getLast',
                    'serialize',
                    'unserialize'
                ]
            )->getMockForAbstractClass();

        //Work Around For Final Count Method
        $reflectionMethod = new ReflectionProperty('\Phalcon\Mvc\Model\Resultset', '_count');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->setValue($mockResultSet, count($dataSet));

        //Work Around For Final Seek
        $reflectionProperty = new ReflectionProperty('\Phalcon\Mvc\Model\Resultset', '_rows');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($mockResultSet, $dataSet);

        $sharedData = new stdClass();
        $sharedData->pos = 0;
        $sharedData->data = $dataSet;

        $mockResultSet->method('getFirst')
            ->willReturnCallback(function () use ($sharedData) {
                if (empty($sharedData->data)) {
                    return false;
                }

                $arrayKeys = array_keys($sharedData->data);
                return $sharedData->data[$arrayKeys[0]];
            });

        $mockResultSet->method('getLast')
            ->willReturnCallback(function () use ($sharedData) {
                if (empty($sharedData->data)) {
                    return false;
                }

                return array_reverse($sharedData->data)[0];
            });

        $mockResultSet->method('valid')
            ->willReturnCallback(
                function () use ($sharedData) {
                    return $sharedData->pos < count($sharedData->data);
                }
            );

        $mockResultSet->method('current')
            ->willReturnCallback(
                function () use ($sharedData) {
                    return $sharedData->data[$sharedData->pos];
                }
            );

        $mockResultSet->method('key')
            ->willReturnCallback(
                function () use ($sharedData) {
                    return array_keys($sharedData->data)[$sharedData->pos];
                }
            );

        $mockResultSet->method('next')
            ->willReturnCallback(
                function () use ($sharedData) {
                    $sharedData->pos++;
                }
            );

        $mockResultSet->method('toArray')
            ->willReturn($dataSet);

        return $mockResultSet;
    }
}
