<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\payment\PaymentNotFound;
use App\lib\Time;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class Homepage extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $queryParams = $this->getQueryParams();

        $page = $queryParams['page'];
        $id = $queryParams['query'];
        $status = $queryParams['status'];
        $from = $queryParams['from'];
        $to = $queryParams['to'];

        $max = 5;

        $data = [
            'paymentYear' => null,
            'paymentStart' => null,
            'dues' => null,
            'transactions' => null,
            'settings' => null,
            'paginator' => null,
            'currentPage' => $page,
            'query' => $id,
            'from' => $from,
            'to' => $to,
            'status' => $status,
        ];

        try {

            $settings = $this->paymentService->findById(1);

            $result = $this->transactionService->getPayments(
                $page,
                $max,
                $id,
                $status,
                $from,
                $to,
            );

            $startOfPaymentYear = Time::getCurrentYear($settings->getStart());

            $dues = $this->duesService->getMonthlyDues($startOfPaymentYear);

            $data['paymentYear'] = Time::getYearSpan((int)$startOfPaymentYear);
            $data['paymentStart'] = $startOfPaymentYear;
            $data['dues'] = $dues;
            $data['transactions'] = $result->getItems();
            $data['settings'] = $settings;
            $data['paginator'] = $result;

        } catch (PaymentNotFound $paymentNotFound) {
            $this->addErrorMessage('Payment Settings is not set');
        } catch (Exception $exception) {
            $this->addErrorMessage('An  Internal Error Has Occurred, pleas check logs');
        }

        return $this->view('admin/pages/payments.html', $data);
    }
}