<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\EventListener\BuildView;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\OptionalTemplateListener;
use Libero\ViewsBundle\Views\TemplateView;
use UnexpectedValueException;
use function GuzzleHttp\Psr7\mimetype_from_filename;
use function in_array;
use function Libero\LiberoPageBundle\absolute_xlink_href;
use function Libero\ViewsBundle\array_has_key;
use function sprintf;

final class GraphicImageListener
{
    use OptionalTemplateListener;

    protected function handle(Element $object, TemplateView $view) : TemplateView
    {
        try {
            $uri = absolute_xlink_href($object);
        } catch (UnexpectedValueException $e) {
            return $view;
        }

        if (!in_array($uri->getScheme(), ['http', 'https'], true)) {
            return $view;
        }

        if ('image/jpeg' !== sprintf('%s/%s', $object->getAttribute('mimetype'), $object->getAttribute('mime-subtype'))
            &&
            'image/jpeg' !== mimetype_from_filename((string) $uri)
        ) {
            return $view;
        }

        $image = ['src' => $uri, 'alt' => ''];

        $altText = $object->ownerDocument->xpath()->firstOf('jats:alt-text', $object);

        if ($altText instanceof Element) {
            $image['alt'] = (string) $altText;
        }

        return $view->withArgument('image', $image);
    }

    protected function template() : string
    {
        return '@LiberoPatterns/image.html.twig';
    }

    protected function canHandleElement(string $element) : bool
    {
        return '{http://jats.nlm.nih.gov}graphic' === $element;
    }

    protected function canHandleArguments(array $arguments) : bool
    {
        return !array_has_key($arguments, 'image');
    }
}
