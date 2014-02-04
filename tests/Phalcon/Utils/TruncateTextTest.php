<?php
namespace Phalcon\Tests\Utils;

class TruncateTextTest extends \PHPUnit_Framework_TestCase
{
    public function testPassingTextWithLessCharsThenLimitShouldReturnNontruncatedText()
    {
        $limit = 50;
        $break = '.';
        $pad = '...';

        $originalText = 'some short text';
        $this->assertEquals($originalText, \Phalcon\Utils\TruncateText::truncateText('some short text', $limit, $break, $pad));
    }

    public function testTruncatingTextBreakingOnDotShouldReturnExpectedTruncatedTextLength()
    {
        $limit = 50;
        $break = '.';
        $pad = '...';

        $originalText = <<<HEREDOC
<p>
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at libero molestie, venenatis justo pulvinar, congue velit. Sed in felis orci. In rhoncus consectetur est sit amet lacinia. Morbi pellentesque rhoncus sem, at interdum turpis aliquam quis. Morbi eget dui nulla. Mauris vitae lectus eu risus vestibulum ornare. Sed vel augue nibh. Donec mauris augue, suscipit cursus tortor ac, dignissim tincidunt libero. Maecenas adipiscing lorem commodo elit ornare, eget euismod orci dignissim. Donec ut purus sit amet odio malesuada ultricies quis sit amet nisi. Fusce interdum bibendum turpis, sed tempus erat. Morbi a velit et tortor vestibulum fringilla ut ac diam. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Ut in sem in dui dictum mattis. Sed ultrices sed neque ac ultricies. Suspendisse eget est eleifend, fermentum felis vel, ornare lorem.
</p>
HEREDOC;

        $truncatedText = \Phalcon\Utils\TruncateText::truncateText($originalText, $limit, $break, $pad);
        $length = 58; // limit + ...
        $this->assertEquals($length, strlen($truncatedText));
        $this->assertEquals('Lorem ipsum dolor sit amet, consectetur adipiscing elit' . $pad, $truncatedText);
    }

    public function testTruncatingTextBreakingOnSpaceShouldReturnExpectedTruncatedTextLength()
    {
        $limit = 50;
        $break = ' ';
        $pad = '...';

        $originalText = <<<HEREDOC
<p>
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at libero molestie, venenatis justo pulvinar, congue velit. Sed in felis orci. In rhoncus consectetur est sit amet lacinia. Morbi pellentesque rhoncus sem, at interdum turpis aliquam quis. Morbi eget dui nulla. Mauris vitae lectus eu risus vestibulum ornare. Sed vel augue nibh. Donec mauris augue, suscipit cursus tortor ac, dignissim tincidunt libero. Maecenas adipiscing lorem commodo elit ornare, eget euismod orci dignissim. Donec ut purus sit amet odio malesuada ultricies quis sit amet nisi. Fusce interdum bibendum turpis, sed tempus erat. Morbi a velit et tortor vestibulum fringilla ut ac diam. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Ut in sem in dui dictum mattis. Sed ultrices sed neque ac ultricies. Suspendisse eget est eleifend, fermentum felis vel, ornare lorem.
</p>
HEREDOC;
        $truncatedText = \Phalcon\Utils\TruncateText::truncateText($originalText, $limit, $break, $pad);
        $length = 53; // limit + ...
        $this->assertEquals($length, strlen(\Phalcon\Utils\TruncateText::truncateText($originalText, $limit, $break, $pad)));
        $this->assertEquals('Lorem ipsum dolor sit amet, consectetur adipiscing' . $pad, $truncatedText);
    }
} 