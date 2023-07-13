<?php

namespace App\controller;

use App\lib\Filter;
use App\Lib\Image;
use App\lib\Time;
use App\model\PaymentModel;
use Slim\Views\Twig;

class ApiController extends Controller {

    /**
     * End point to save image. 
     */
    public function upload($request, $response, $args) {
        $uploadPath = './uploads/';
        $imageName = Image::store($uploadPath, $_FILES['image']);

        $payload = json_encode(['path' => '/uploads/' . $imageName]);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    
}
