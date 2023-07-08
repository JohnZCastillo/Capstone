<?php
use App\controller\UserController;
use Slim\Factory\AppFactory;

require './vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', [UserController::class ,'home']);

$app->run();