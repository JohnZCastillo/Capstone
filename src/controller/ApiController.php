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

    public function findBill($request, $response, $args)
    {

        $billId = $args['id'];

        try {

            $bill = $this->billService->findById($billId);

            if(!isset($bill)){
                throw new Exception('Bill Not Found!');
            }

            $payload =json_encode([
                'id' => $bill->getId(),
                'title' => $bill->getExpense()->getTitle(),
                'amount' => $bill->getExpense()->getAmount(),
                'purpose' => $bill->getExpense()->getPurpose(),
                'interval' => 'test',
                'fundId' => $bill->getExpense()->getFund()->getId(),
                'fundName' =>  $bill->getExpense()->getFund()->getTitle(),
            ]);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');

        } catch (Exception $e) {

            $payload = json_encode(['message' => $e->getMessage()]);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }

    public function generateBill($request, $response, $args)
    {

        $content = $request->getParsedBody();

        $billId = $content['bill'];

        try {

            $bill = $this->billService->findById($billId);

            if(!isset($bill)){
                throw new Exception('Bill Not Found!');
            }

            if($bill->isArchived()){
                throw new Exception("Bill is set to archived, please make the bill active first");
            }

            $expense = $bill->getExpense();

            $newExpenseBill = new ExpenseModel();
            $newExpenseBill->setStatus(BudgetStatus::pending());
            $newExpenseBill->setAmount($expense->getAmount());
            $newExpenseBill->setTitle($expense->getTitle());
            $newExpenseBill->setFund($expense->getFund());
            $newExpenseBill->setPurpose($expense->getPurpose());
            $newExpenseBill->setBill($bill);

            $this->expenseService->save($newExpenseBill);

        } catch (Exception $e) {
           $this->flashMessages->addMessage('errorMessage',$e->getMessage());
        }

        return $response
            ->withHeader('Location', "/admin/budget")
            ->withStatus(302);

    }

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
