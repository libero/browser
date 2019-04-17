<?php

declare(strict_types=1);

namespace tests\Libero\LiberoPageBundle;

use FluentDOM\DOM\Element;
use PHPUnit\Framework\TestCase;
use function Libero\LiberoPageBundle\resolve_xlink_href;

final class ResolveXlinkHrefTest extends TestCase
{
    use XmlTestCase;

    /**
     * @test
     * @dataProvider xlinkHrefProvider
     */
    public function it_determines_the_xlink_href_for_an_element(
        string $xml,
        ?string $documentUri,
        string $selector,
        string $expected
    ) : void {
        /** @var Element $element */
        $element = $this->loadDocument($xml, $documentUri)->xpath()->firstOf($selector);

        $this->assertSame($expected, (string) resolve_xlink_href($element));
    }

    public function xlinkHrefProvider() : iterable
    {
        yield 'multiple absolutes URIs' => [
            <<<XML
<foo xml:base="http://example.com/foo/" xmlns:xlink="http://www.w3.org/1999/xlink">
    <bar xml:base="http://example.com/bar/">
        <baz xlink:href="http://example.com/baz/"/>
    </bar>
</foo>
XML
            ,
            null,
            '//baz',
            'http://example.com/baz/',
        ];

        yield 'multiple absolutes URIs, document URI' => [
            <<<XML
<foo xml:base="http://example.com/foo/" xmlns:xlink="http://www.w3.org/1999/xlink">
    <bar xml:base="http://example.com/bar/">
        <baz xlink:href="http://example.com/baz/"/>
    </bar>
</foo>
XML
            ,
            'http://example.com/dir/file.xml',
            '//baz',
            'http://example.com/baz/',
        ];

        yield 'two absolutes and a root relative' => [
            <<<XML
<foo xml:base="http://example.com/foo/" xmlns:xlink="http://www.w3.org/1999/xlink">
    <bar xml:base="http://example.com/bar/">
        <baz xlink:href="/baz/"/>
    </bar>
</foo>
XML
            ,
            null,
            '//baz',
            'http://example.com/baz/',
        ];

        yield 'two absolutes and a root relative, document URI' => [
            <<<XML
<foo xml:base="http://example.com/foo/" xmlns:xlink="http://www.w3.org/1999/xlink">
    <bar xml:base="http://example.com/bar/">
        <baz xlink:href="/baz/"/>
    </bar>
</foo>
XML
            ,
            'http://example.com/dir/file.xml',
            '//baz',
            'http://example.com/baz/',
        ];

        yield 'absolute and a root relative' => [
            <<<XML
<foo xml:base="http://example.com/foo/" xmlns:xlink="http://www.w3.org/1999/xlink">
    <bar>
        <baz xlink:href="/baz/"/>
    </bar>
</foo>
XML
            ,
            null,
            '//baz',
            'http://example.com/baz/',
        ];

        yield 'absolute and a root relative, document URI' => [
            <<<XML
<foo xml:base="http://example.com/foo/" xmlns:xlink="http://www.w3.org/1999/xlink">
    <bar>
        <baz xlink:href="/baz/"/>
    </bar>
</foo>
XML
            ,
            'http://example.com/dir/file.xml',
            '//baz',
            'http://example.com/baz/',
        ];

        yield 'absolute and a relative' => [
            <<<XML
<foo xml:base="http://example.com/foo/" xmlns:xlink="http://www.w3.org/1999/xlink">
    <bar>
        <baz xlink:href="baz/"/>
    </bar>
</foo>
XML
            ,
            null,
            '//baz',
            'http://example.com/foo/baz/',
        ];

        yield 'absolute and a relative, document URI' => [
            <<<XML
<foo xml:base="http://example.com/foo/" xmlns:xlink="http://www.w3.org/1999/xlink">
    <bar>
        <baz xlink:href="baz/"/>
    </bar>
</foo>
XML
            ,
            'http://example.com/dir/file.xml',
            '//baz',
            'http://example.com/foo/baz/',
        ];

        yield 'no ancestors' => [
            <<<XML
<foo xlink:href="foo/" xmlns:xlink="http://www.w3.org/1999/xlink"/>
XML
            ,
            null,
            '/foo',
            'foo/',
        ];

        yield 'no ancestors, document URI' => [
            <<<XML
<foo xlink:href="foo/" xmlns:xlink="http://www.w3.org/1999/xlink"/>
XML
            ,
            'http://example.com/dir/file.xml',
            '/foo',
            'http://example.com/dir/foo/',
        ];

        yield 'multiple ancestors' => [
            <<<XML
<foo xmlns:xlink="http://www.w3.org/1999/xlink">
    <bar>
        <baz xlink:href="baz/"/>
    </bar>
</foo>
XML
            ,
            null,
            '//baz',
            'baz/',
        ];

        yield 'multiple ancestors, document URI' => [
            <<<XML
<foo xmlns:xlink="http://www.w3.org/1999/xlink">
    <bar>
        <baz xlink:href="baz/"/>
    </bar>
</foo>
XML
            ,
            'http://example.com/dir/file.xml',
            '//baz',
            'http://example.com/dir/baz/',
        ];
    }
}
