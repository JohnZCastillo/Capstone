<?php

declare(strict_types=1);

namespace App\controller\user\payments;

use App\controller\user\UserAction;
use Psr\Http\Message\ResponseInterface as Response;

class UnpaidDues extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $user = $this->getLoginUser();

        $settings = $this->paymentService->findById(1);

        $unpaidDues =  $this->transactionService->getUnpaid($user, $this->duesService, $settings);

        $data = [
            'items' => $unpaidDues['items'],
            'total' => $unpaidDues['total'],
        ];

        return $this->view('user/pages/unpaid-dues.html',$data);
    }
}