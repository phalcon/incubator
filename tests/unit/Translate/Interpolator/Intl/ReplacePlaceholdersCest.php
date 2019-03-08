<?php

namespace Phalcon\Test\Unit\Translate\Interpolator\Intl;

use Phalcon\Translate\Interpolator\Intl;
use Phalcon\Translate\Exception;
use UnitTester;
use NumberFormatter;

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
        // thousands separator is "," for en_US
        $I->assertEquals('I have 1,000 apples and my name is John.', $interpolator->replacePlaceholders($stringFrom, [
            'number_apples' => 1000,
            'name' => 'John'
        ]));
        
        // thousands separator is usually " " (blank space) for fr_FR
        // depending on system settings it can also be an unbreakable-space
        // retrieve it through NumberFormatter API
        $numberformatter = new NumberFormatter('fr_FR', NumberFormatter::PATTERN_DECIMAL);
        $thousand_separator = $numberformatter->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
        unset($numberformatter);
        
        $interpolator = new Intl('fr_FR');
        $stringFrom = "{number_apples, plural, =0{Je n'ai aucune pomme} =1{J'ai une pomme} other{J'ai # pommes}} et mon nom est {name}.";
        $I->assertEquals("J'ai 1{$thousand_separator}000 pommes et mon nom est John.", $interpolator->replacePlaceholders($stringFrom, [
            'number_apples' => 1000,
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
    public function translateInterpolatorIntlReplacePlaceholdersBadString(UnitTester $I)
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
    }
    
    /**
     * Tests Phalcon\Translate\Interpolator\Intl ::
     * replacePlaceholders()
     *
     * @param UnitTester $I
     *
     * @since  2019-03-07
     */
    public function translateInterpolatorIntlReplacePlaceholdersBadPlaceholders(UnitTester $I)
    {
        $I->wantToTest('Translate\Interpolator\Intl - replacePlaceholders() throws an exception when placeholders data is illegal');
        $I->expectThrowable(
            new Exception("No strategy to convert the value given for the argument with key '0' is available: U_ILLEGAL_ARGUMENT_ERROR", 1),
            function () {
                $interpolator = new Intl('en_US');
                $stringFrom = 'My name is {name}.';
                // [[]] is an illegal argument
                $interpolator->replacePlaceholders($stringFrom, [[]]);
            }
        );
    }
}
