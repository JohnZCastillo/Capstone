<?php

declare(strict_types=1);

namespace App\controller;

use App\model\LogsModel;
use App\model\UserModel;
use App\service\LogsService;
use App\service\UserService;
use DateTime;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class Action
{
    protected LoggerInterface $logger;

    protected Request $request;

    protected Response $response;

    protected array $args;


    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action();
        } catch (Exception $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }

    /**
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @return array|object
     */
    protected function getFormData()
    {
        return $this->request->getParsedBody();
    }

    /**
     * @return array|object
     */
    protected function getQueryParams()
    {

        $queryParams = $this->request->getQueryParams();

        $queryParams['page'] = empty($queryParams['page']) ? 1 : $queryParams['page'] ;
        $queryParams['id'] = empty($queryParams['id']) ? null : $queryParams['id'] ;
        $queryParams['status'] = empty($queryParams['status']) ? null : $queryParams['status'] ;
        $queryParams['from'] = empty($queryParams['from']) ? null : $queryParams['from'] ;
        $queryParams['to'] = empty($queryParams['to']) ? null : $queryParams['to'] ;
        $queryParams['query'] = empty($queryParams['query']) ? null : $queryParams['query'] ;
        $queryParams['block'] = empty($queryParams['block']) ? null : $queryParams['block'] ;
        $queryParams['lot'] = empty($queryParams['lot']) ? null : $queryParams['lot'] ;

        if(isset($queryParams['status'])){
            $queryParams['status'] = $queryParams['status'] == 'ALL' ? null : $queryParams['status'];
        }

        if(isset($queryParams['block'])){
            $queryParams['block'] = $queryParams['block'] == 'ALL' ? null : $queryParams['block'];
        }

        if(isset($queryParams['lot'])){
            $queryParams['lot'] = $queryParams['lot'] == 'ALL' ? null : $queryParams['lot'];
        }

        return $queryParams;
    }

    /**
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * @param array|object|null $data
     */
    protected function respondWithData($data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($payload);
    }

    protected function respond(ActionPayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($payload->getStatusCode());
    }

    /**
     * Returns a view
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function  view(string $template, array $data): Response
    {
        $view = Twig::fromRequest($this->request);

        return $view->render($this->response, $template,$data);
    }

    protected function redirect(string $location,int $status = 302): Response
    {
        return  $this->response
            ->withHeader('Location', $location)
            ->withStatus($status);
    }




}