<?php

declare(strict_types=1);

namespace Tests;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

session_start();

class TestCase extends PHPUnit_TestCase
{
    use ProphecyTrait;

    /**
     * @return App
     * @throws Exception
     */
    protected function getAppInstance(): App
    {
        // Instantiate PHP-DI ContainerBuilder
        $containerBuilder = new ContainerBuilder();

        // Container intentionally not compiled for tests.

        // Set up settings
        $settings = require __DIR__ . '/../dependencies/container.php';
        $settings($containerBuilder);

        // Build PHP-DI Container instance
        $container = $containerBuilder->build();


        $twig = Twig::create(__DIR__ . '/../public/views/', ['cache' => false, 'debug' => true]);


        // Instantiate the app
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        $app->add(TwigMiddleware::create($app, $twig));


        // Register routes
        $routes = require __DIR__ . '/../app/routes.php';
        $routes($app);

        return $app;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $headers
     * @param array $cookies
     * @param array $serverParams
     * @return Request
     */
    protected function createRequest(
        string $method,
        string $path,
        array  $headers = ['HTTP_ACCEPT' => 'application/json'],
        array  $cookies = [],
        array  $serverParams = []
    ): Request
    {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }
}