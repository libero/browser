<?php

declare(strict_types=1);

namespace Libero\LiberoPageBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

final class LiberoPageListener
{
    private $routes;

    /**
     * @param array<string,string> $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function onKernelRequest(GetResponseEvent $event) : void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (!isset($this->routes[$route])) {
            $request->attributes->set('libero_page', ['type' => '']);

            return;
        }

        $page = $this->routes[$route];

        if ('content' === $page['type']) {
            $page['content_id'] = $request->attributes->get('id');
        }

        $request->attributes->set('libero_page', $page);
    }
}
