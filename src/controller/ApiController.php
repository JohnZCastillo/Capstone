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

    public function amount($request, $response, $args) {
        
        $body = $request->getParsedBody();
        
        $fromMonth = $body['fromMonth'];
        $toMonth = $body['toMonth'];

        $fromMonth = Time::nowStartMonth($fromMonth);
        $toMonth = Time::nowStartMonth($toMonth);

        $amount = $this->transactionService->getUnpaid(
            $this->getLogin(),
            $this->duesService,
            $this->getPaymentSettings(),
            $fromMonth,
            $toMonth
        );

        $months = Time::getMonths($fromMonth,$toMonth);

        $payload = json_encode(['amount' => $amount['total']]);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');

    }
}
