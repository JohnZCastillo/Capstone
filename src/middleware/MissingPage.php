<?php

namespace App\middleware;

use DI\Container;
use Slim\Interfaces\ErrorRendererInterface;
use Slim\Views\Twig;
use Throwable;

class MissingPage implements ErrorRendererInterface
{

    protected Twig $twig;

    public function __construct(Container $container)
    {
        $this->twig = $container->get('view');
    }

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
         return $this->twig->fetch('missing-page.html');
    }
}