<?php

namespace App\controller;

use App\lib\Image;
use App\lib\Time;
use App\model\budget\ExpenseModel;
use App\model\enum\BudgetStatus;
use DateTime;
use Exception;

class ApiController extends Controller
{

    public function amount($request, $response, $args)
    {

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

        $months = Time::getMonths($fromMonth, $toMonth);

        $payload = json_encode(['amount' => $amount['total']]);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');

    }

    public function changeDetails($request, $response, $args)
    {

        try {

            $body = $request->getParsedBody();

            $userId = $body['userId'];

            $user = $this->userSerivce->findById($userId);

            if ($user == null) {
                throw  new \Exception("User not found");
            }

            if ($this->getLogin()->getId() != $userId) {
                throw new Exception("Cannot Change others Details");
            }

            $user->setEmail($body['email']);
            $user->setName($body['name']);

            $this->userSerivce->save($user);

            $payload = json_encode([
                "email" => $user->getEmail(),
                "name" => $user->getName(),
            ]);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {

            $payload = json_encode(["message" => $e->getMessage()]);

            $response->getBody()->write($payload);

            return $response->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }
    }

}
