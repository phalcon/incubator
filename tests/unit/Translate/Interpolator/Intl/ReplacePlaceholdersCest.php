<?php

namespace Phalcon\Test\Unit\Translate\Interpolator\AssociativeArray;

use Phalcon\Translate\Interpolator\Intl;
use Phalcon\Translate\Exception;
use UnitTester;
use DateTimeImmutable;

/**
 * Class ReplacePlaceholdersCest
 */
class ReplacePlaceholdersCest
{
    /**
     * Tests Phalcon\Translate\Interpolator\Intl ::
     * replacePlaceholders()
     *
     * @param UnitTester $I
     *
     * @since  2019-03-07
     */
    public function translateInterpolatorIntlReplacePlaceholders(UnitTester $I)
    {
        $I->wantToTest('Translate\Interpolator\Intl - replacePlaceholders()');
        $interpolator = new Intl('en_US');
        $stringFrom = 'I have {number_apples, plural, =0{no apples} =1{one apple} other{# apples}} and my name is {name}.';

        $I->assertEquals('I have no apples and my name is John.', $interpolator->replacePlaceholders($stringFrom, [
            'number_apples' => 0,
            'name' => 'John'
        ]));
        $I->assertEquals('I have one apple and my name is Richard.', $interpolator->replacePlaceholders($stringFrom, [
            'number_apples' => 1,
            'name' => 'Richard'
        ]));
        $I->assertEquals('I have 5 apples and my name is John.', $interpolator->replacePlaceholders($stringFrom, [
            'number_apples' => 5,
            'name' => 'John'
        ]));
    }
    
    /**
     * Tests Phalcon\Translate\Interpolator\Intl ::
     * replacePlaceholders()
     *
     * @param UnitTester $I
     *
     * @since  2019-03-07
     */
    public function translateInterpolatorIntlReplacePlaceholdersBadArguments(UnitTester $I)
    {
        $I->wantToTest('Translate\Interpolator\Intl - replacePlaceholders() throws an exception when fails to create a MessageFormatter');
        
        $I->expectThrowable(
            new Exception("Unable to instantiate a MessageFormatter. Check locale and string syntax."),
            function () {
                $interpolator = new Intl('en_US');
                $stringFrom = 'My name is {name, incorrect}.';
                $interpolator->replacePlaceholders($stringFrom, ['whatever']);
            }
        );
        
        $I->expectThrowable(
            new Exception("No strategy to convert the value given for the argument with key '0' is available: U_ILLEGAL_ARGUMENT_ERROR", 1),
            function () {
                $interpolator = new Intl('xx_XX');
                $stringFrom = 'My name is {name}.';
                // [[]] is an illegal argument
                var_dump($interpolator->replacePlaceholders($stringFrom, [[]]));
            }
        );
        
    }
}
