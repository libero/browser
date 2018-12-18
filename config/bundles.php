<?php

declare(strict_types=1);

return [
    Csa\Bundle\GuzzleBundle\CsaGuzzleBundle::class => ['all' => true],
    Libero\ContentPageBundle\ContentPageBundle::class => ['all' => true],
    Libero\LiberoPatternsBundle\LiberoPatternsBundle::class => ['all' => true],
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
];
