<?php

declare(strict_types=1);

namespace Libero\ContentPageBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

final class ContentController
{
    public function __invoke(string $id) : Response
    {
        return new Response("<html><body>${id}</body></html>");
    }
}
