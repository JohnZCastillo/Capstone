<?php

namespace App\controller;

use App\Lib\Image;
use App\lib\Time;
use App\model\PaymentModel;
use Slim\Views\Twig;

class AdminController extends Controller{

    public function home($request, $response, $args) {

        // get the query params
        $queryParams = $request->getQueryParams();

        $settings = $this->paymentService->findById(1);

        // if page is present then set value to page otherwise to 1
        $page = isset($queryParams['page']) ? $queryParams['page'] : 1;

        if(isset(($queryParams['from'])) && $queryParams['from'] == null){
            unset($queryParams['from']);
        }

        if(isset(($queryParams['to'])) && $queryParams['to'] == null){
            unset($queryParams['to']);
        }

        $filter['from'] = isset($queryParams['from']) ? Time::nowStartMonth($queryParams['from']): null;
        $filter['to'] = isset($queryParams['to']) ? Time::nowEndMonth($queryParams['to']) : null;
        $filter['status'] =  isset($queryParams['status']) ? $queryParams['status'] : null;

        $id = isset($queryParams['query']) ? $queryParams['query'] : null;

        // max transaction per page
        $max = 4;

        $view = Twig::fromRequest($request);

        //Get Transaction
        $result = $this->transactionService->getAll($page, $max, $id,$filter);

        $transactions = $result['transactions'];

        $data = [
            'transactions' => $transactions,
            'totalTransaction' => $result['totalTransaction'],
            'transactionPerPage' => $max,
            'currentPage' => $page,
            'query' => $id,
            'from' =>  isset($queryParams['from']) ? $queryParams['from'] : null,
            'to' => isset($queryParams['to']) ? $queryParams['to'] : null,
            'status' =>  isset($queryParams['status']) ? $queryParams['status'] : null,
            'totalPages' => ceil(($result['totalTransaction']) / $max),
            'settings' => $settings
        ];

        return $view->render($response, 'pages/admin-home.html', $data);
    }

    /**
     *   Get Transaction Base on id
     */
    public function transaction($request, $response, $args) {
        
        $view = Twig::fromRequest($request);

        $transaction = $this->transactionService->findById($args['id']);

        $user = $transaction->getUser();

        return $view->render($response, 'pages/admin-transaction.html', [
            'transaction' => $transaction,
            'receipts' => $transaction->getReceipts(),
            'user' => $user,
        ]);
    }

    public function rejectPayment($request, $response, $args) {

        $view = Twig::fromRequest($request);

        $id = $request->getParsedBody()['id'];
        $message = $request->getParsedBody()['message'];

        // get the transaction form db
        $transaction = $this->transactionService->findById($id);

        //get the owner of the transaction
        $user = $transaction->getUser();

        // set transctio to rejected
        $transaction->setStatus('REJECTED');

        // save transaction
        $this->transactionService->save($transaction);

        //save logs
        $this->logsService->log($transaction,$user,$message,'REJECTED');

        return $response
            ->withHeader('Location', "/admin/transaction/$id")
            ->withStatus(302);
    }

    public function approvePayment($request, $response, $args) {

        $view = Twig::fromRequest($request);

        $id = $request->getParsedBody()['id'];
        
        $message = "Payment was approved";
        
        // get the transaction form db
        $transaction = $this->transactionService->findById($id);
        
        //get the owner of the transaction
        $user = $transaction->getUser();
        
        //array of reference number
        $fields = $request->getParsedBody()['field'];

        //array of transaction receipts
        $reciepts = $transaction->getReceipts();
  
        for ($i=0; $i <count($reciepts) ; $i++) { 
            $this->receiptService->confirm($reciepts[$i],$fields[$i]);
        }

        // set transctio to rejected
        $transaction->setStatus('APPROVED');

        // save transaction
        $this->transactionService->save($transaction);

        //save logs
        $this->logsService->log($transaction,$user,$message,'APPROVED');

        return $response
            ->withHeader('Location', "/admin/transaction/$id")
            ->withStatus(302);
    }

    public function paymentSettings($request, $response, $args){

        $view = Twig::fromRequest($request);

        $id = $request->getParsedBody()['id'];
        $name = $request->getParsedBody()['name'];
        $number = $request->getParsedBody()['number'];
        $start = $request->getParsedBody()['start'];

        $settings = new PaymentModel();

        //find settings if id is not null
        if($id != null){
            $settings = $this->paymentService->findById($id);
        }

        //update qr
        if(isset($_FILES['qr']) && $_FILES['qr']['error'] === UPLOAD_ERR_OK){
            $path = './uploads/';
            $settings->setQr(Image::store($path,$_FILES['qr']));
        }

        $settings->setAccountName($name);
        $settings->setAccountNumber($number);
        $settings->setStart(Time::startMonth($start));

        $this->paymentService->save($settings);
        
        return $response
            ->withHeader('Location', "/admin")
            ->withStatus(302);
    }

}