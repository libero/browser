<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Libero\LiberoPageBundle\text_direction;

final class TextDirectionTest extends TestCase
{
    /**
     * @test
     * @dataProvider textDirectionProvider
     */
    public function it_returns_the_text_direction_of_a_locale(string $locale, string $expected) : void
    {
        $this->assertSame($expected, text_direction($locale));
    }

    public function textDirectionProvider() : iterable
    {
        yield 'en' => ['en', 'ltr'];
        yield 'en-GB' => ['en-GB', 'ltr'];
        yield 'ar' => ['ar', 'rtl'];
        yield 'ar-EG' => ['ar-EG', 'rtl'];
        yield 'unknown' => ['foo', 'ltr'];
    }

    /**
     * @test
     */
    public function it_fails_on_an_invalid_locale() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'foo bar' is not a valid locale identifier");

        text_direction('foo bar');
    }
}
