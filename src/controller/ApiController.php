<?php

namespace App\controller;

use App\lib\Filter;
use App\lib\Image;
use App\lib\Time;
use App\model\DuesModel;
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

    public function addDue($request, $response, $args) {

        $month = $request->getParsedBody()['month'];
        $amount = $request->getParsedBody()['amount'];

        $due = new DuesModel();
        $due->setAmount($amount);
        $due->setMonth(Time::startMonth($month));

        $this->duesService->update($due);

        $payload = json_encode(['message' => "ok"]);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
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

    public function user($request, $response, $args) {

        try {
            $body = $request->getParsedBody();

            $email = $body['email'];

            $user = $this->userSerivce->findByEmail($email);

            if($user == null){
             throw  new \Exception("User not found");
            }

            $data = ['name' => $user->getName(),
                    'payment' => $user->getPrivileges()->getAdminPayment(),
                    'issue' => $user->getPrivileges()->getAdminIssues(),
                    'announcement' => $user->getPrivileges()->getAdminAnnouncement(),
                    'user' => $user->getPrivileges()->getAdminUser(),
            ];

            $payload = json_encode($data);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }catch (\Exception $e){
            return $response->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }


    }
}
