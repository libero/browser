<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\SimplifiedViewConverterListener;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function Libero\ViewsBundle\array_has_key;

final class ItemTeaserHrefListener
{
    use SimplifiedViewConverterListener;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        $xpath = $object->ownerDocument->xpath();

        /** @var string $id */
        $id = $xpath->evaluate('string(/libero:item/libero:meta/libero:id)');
        /** @var string $service */
        $service = $xpath->evaluate('string(/libero:item/libero:meta/libero:service)');

        if ('' === $id || '' === $service) {
            return $view;
        }

        return $view->withArgument(
            'href',
            $this->urlGenerator->generate("libero.page.content.{$service}", ['id' => $id])
        );
    }

    protected function canHandleTemplate(?string $template) : bool
    {
        return '@LiberoPatterns/teaser.html.twig' === $template;
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://libero.pub}item' === $element;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'href');
    }
}
