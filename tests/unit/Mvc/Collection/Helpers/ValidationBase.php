<?php

namespace Phalcon\Test\Mvc\Collection\Helpers;

use DateTime;
use Phalcon\Test\Collections\Users;
use Phalcon\Mvc\Model\Message;
use UnitTester;

class ValidationBase
{
    protected function success(UnitTester $I)
    {
        $collection = new Users();
        $collection->email = 'fuego@hotmail.com';
        $collection->created_at = (new DateTime())->format('Y-m-d H:i:s');
        $collection->status = 'A';
        $I->assertTrue($collection->save());
    }

    protected function presenceOf(UnitTester $I)
    {
        $collection = new Users();
        $collection->email = 'diego@hotmail.com';
        $collection->created_at = null;
        $collection->status = 'A';
        $I->assertFalse($collection->save());

        $expected = [
            Message::__set_state(
                [
                    '_message' => 'Field created_at is required',
                    '_field'   => 'created_at',
                    '_type'    => 'PresenceOf',
                    '_code'    => 0,
                ]
            ),
        ];

        $I->assertEquals($expected, $collection->getMessages());
    }

    protected function email(UnitTester $I)
    {
        $collection = new Users();
        $collection->email = 'fuego?=';
        $collection->created_at = (new DateTime())->format('Y-m-d H:i:s');
        $collection->status = 'A';
        $I->assertFalse($collection->save());

        $expected = [
            Message::__set_state(
                [
                    '_message' => 'Field email must be an email address',
                    '_field'   => 'email',
                    '_type'    => 'Email',
                    '_code'    => 0,
                ]
            ),
        ];

        $I->assertEquals($expected, $collection->getMessages());
    }

    protected function exclusionIn(UnitTester $I)
    {
        $collection = new Users();
        $collection->email = 'serghei@hotmail.com';
        $collection->created_at = (new DateTime())->format('Y-m-d H:i:s');
        $collection->status = 'P';
        $I->assertFalse($collection->save());

        $expected = [
            Message::__set_state(
                [
                    '_message' => 'Field status must not be a part of list: P, I, w',
                    '_field'   => 'status',
                    '_type'    => 'ExclusionIn',
                    '_code'    => 0,
                ]
            ),
            Message::__set_state(
                [
                    '_message' => 'Field status must be a part of list: A, y, Z',
                    '_field'   => 'status',
                    '_type'    => 'InclusionIn',
                    '_code'    => 0,
                ]
            ),
        ];

        $I->assertEquals($expected, $collection->getMessages());
    }

    protected function inclusionIn(UnitTester $I)
    {
        $collection = new Users();
        $collection->email = 'serghei@hotmail.com';
        $collection->created_at = (new DateTime())->format('Y-m-d H:i:s');
        $collection->status = 'R';
        $I->assertFalse($collection->save());

        $expected = [
            Message::__set_state(
                [
                    '_message' => 'Field status must be a part of list: A, y, Z',
                    '_field'   => 'status',
                    '_type'    => 'InclusionIn',
                    '_code'    => 0,
                ]
            ),
        ];

        $I->assertEquals($expected, $collection->getMessages());
    }

    protected function uniqueness1(UnitTester $I)
    {
        $collection = new Users();
        $collection->email = 'jurigag@hotmail.com';
        $collection->created_at = (new DateTime())->format('Y-m-d H:i:s');
        $collection->status = 'A';
        $I->assertTrue($collection->save());

        $collection = new Users();
        $collection->email = 'jurigag@hotmail.com';
        $collection->created_at = (new DateTime())->format('Y-m-d H:i:s');
        $collection->status = 'A';
        $I->assertFalse($collection->save());

        $expected = [
            Message::__set_state(
                [
                    '_message' => 'Field email must be unique',
                    '_field'   => 'email',
                    '_type'    => 'Uniqueness',
                    '_code'    => 0,
                ]
            ),
        ];

        $I->assertEquals($expected, $collection->getMessages());
    }

    protected function regex(UnitTester $I)
    {
        $collection = new Users();
        $collection->email = 'andres@hotmail.com';
        $collection->created_at = (new DateTime())->format('Y-m-d H:i:s');
        $collection->status = 'y';
        $I->assertFalse($collection->save());

        $expected = [
            Message::__set_state(
                [
                    '_message' => 'Field status does not match the required format',
                    '_field'   => 'status',
                    '_type'    => 'Regex',
                    '_code'    => 0,
                ]
            ),
        ];

        $I->assertEquals($expected, $collection->getMessages());
    }

    protected function tooLong(UnitTester $I)
    {
        $collection = new Users();
        $collection->email = str_repeat('a', 50).'@hotmail.com';
        $collection->created_at = (new DateTime())->format('Y-m-d H:i:s');
        $collection->status = 'A';
        $I->assertFalse($collection->save());

        $expected = [
            Message::__set_state(
                [
                    '_message' => 'Field email must not exceed 50 characters long',
                    '_field'   => 'email',
                    '_type'    => 'TooLong',
                    '_code'    => 0,
                ]
            ),
        ];

        $I->assertEquals($expected, $collection->getMessages());
    }

    protected function tooShort(UnitTester $I)
    {
        $collection = new Users();
        $collection->email = 'a@b.c';
        $collection->created_at = (new DateTime())->format('Y-m-d H:i:s');
        $collection->status = 'A';
        $I->assertFalse($collection->save());

        $expected = [
            Message::__set_state(
                [
                    '_message' => 'Field email must be at least 7 characters long',
                    '_field'   => 'email',
                    '_type'    => 'TooShort',
                    '_code'    => 0,
                ]
            ),
        ];

        $I->assertEquals($expected, $collection->getMessages());
    }
}
