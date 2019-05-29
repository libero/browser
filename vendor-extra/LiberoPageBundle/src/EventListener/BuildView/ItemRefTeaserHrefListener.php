<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\TemplateView;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewBuildingListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function Libero\ViewsBundle\array_has_key;

final class ItemRefTeaserHrefListener
{
    use ViewBuildingListener;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    protected function handle(Element $object, TemplateView $view) : View
    {
        /** @var string $id */
        $id = $object->getAttribute('id');
        /** @var string $service */
        $service = $object->getAttribute('service');

        if ('' === $id || '' === $service) {
            return $view;
        }

        return $view->withArgument(
            'href',
            $this->urlGenerator->generate("libero.page.content.{$service}", ['id' => $id])
        );
    }

    protected function template() : string
    {
        return '@LiberoPatterns/teaser.html.twig';
    }

    protected function canHandleElement(Element $element) : bool
    {
        return '{http://libero.pub}item-ref' === $element->clarkNotation();
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'href');
    }
}
