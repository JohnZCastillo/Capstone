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

    /**
     * End point to save image.
     */
    public function upload($request, $response, $args)
    {
        $uploadPath = './uploads/';
        $imageName = Image::store($uploadPath, $_FILES['image']);

        $payload = json_encode(['path' => '/uploads/' . $imageName]);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function addDue($request, $response, $args)
    {

        try {
            $month = $request->getParsedBody()['month'];
            $amount = $request->getParsedBody()['amount'];
            $year = $request->getParsedBody()['dueYear'];

            $due = $this->duesService->createDue(Time::startMonth($month));
            $due->setAmount($amount);
            $due->setMonth(Time::startMonth($month));

            $this->duesService->save($due);

            $dues = $this->getDues($year);

            $payload = json_encode($dues);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');

        } catch (Exception $e) {

            $payload = json_encode(['message' => $e->getMessage()]);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }


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


    public function yearDues($request, $response, $args)
    {

        try {

            $year = $request->getParsedBody()['dueYear'];

            $dues = $this->getDues($year);

            $payload = json_encode($dues);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');

        } catch (Exception $e) {

            $payload = json_encode(['message' => $e->getMessage()]);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
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

    public function forceLogout($request, $response, $args)
    {

        $body = $request->getParsedBody();

        try {
            $session = $body['session'];

            $login = $this->loginHistoryService->getBySession($session);

            if ($login == null) {
                throw new Exception("Login Not Found!");
            }

            if($login->getUser()->getId() != $this->getLogin()->getId()){
                throw new Exception("Access Denied");
            }

            $login->setLogoutDate(new DateTime());
            $this->loginHistoryService->save($login);

            $payload = json_encode(['logout' => $login->getLogoutDate()->format("M d, Y h:i:s a")]);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $payload = json_encode(['message' => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }

    public function user($request, $response, $args)
    {

        try {
            $body = $request->getParsedBody();

            $email = $body['email'];

            $user = $this->userSerivce->findByEmail($email);

            if ($user == null) {
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
        } catch (\Exception $e) {
            return $response->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function blockUser($request, $response, $args)
    {

        try {

            $body = $request->getParsedBody();

            $userId = $body['userId'];

            $user = $this->userSerivce->findById($userId);

            if ($user == null) {
                throw  new \Exception("User not found");
            }

            $user->setIsBlocked(true);

            $this->userSerivce->save($user);

            $payload = json_encode(["message" => "user has been blocked"]);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return $response->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function unblockUser($request, $response, $args)
    {

        try {

            $body = $request->getParsedBody();

            $userId = $body['userId'];

            $user = $this->userSerivce->findById($userId);

            if ($user == null) {
                throw  new \Exception("User not found");
            }

            $user->setIsBlocked(false);

            $this->userSerivce->save($user);

            $payload = json_encode(["message" => "user has been blocked"]);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return $response->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function changePassword($request, $response, $args)
    {

        try {

            $body = $request->getParsedBody();

            if (!isset($body['currentPassword'], $body['newPassword'], $body['confirmPassword'], $body['userId'])) {
                throw new \Exception("missing inputs");
            }

            $userId = $body['userId'];

            $user = $this->userSerivce->findById($userId);

            if ($user == null) {
                throw  new \Exception("User not found");
            }

            $password = $body['currentPassword'];
            $newPassword = $body['newPassword'];
            $confirmPassword = $body['confirmPassword'];

            if ($user->getPassword() != $password) {
                throw new \Exception("Incorrect Password");
            }

            if ($newPassword != $confirmPassword) {
                throw new \Exception("New Password does not match");
            }

            if ($this->getLogin()->getId() != $userId) {
                throw new Exception("Cannot Change others password");
            }

            $user->setPassword($newPassword);

            $this->userSerivce->save($user);

            $payload = json_encode(["message" => "password update"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            $payload = json_encode(["message" => $e->getMessage()]);

            $response->getBody()->write($payload);

            return $response->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }
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
