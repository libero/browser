<?php

declare(strict_types=1);

namespace tests\Libero\ViewsBundle\Views;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\EmptyView;
use Libero\ViewsBundle\Views\EmptyViewConverter;
use Libero\ViewsBundle\Views\ViewConverter;
use PHPUnit\Framework\TestCase;

final class EmptyViewConverterTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_view_converter() : void
    {
        $converter = new EmptyViewConverter();

        $this->assertInstanceOf(ViewConverter::class, $converter);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_view() : void
    {
        $converter = new EmptyViewConverter();

        $element = new Element('foo');

        $this->assertInstanceOf(EmptyView::class, $converter->convert($element, 'template', ['bar' => 'baz']));
    }
}
