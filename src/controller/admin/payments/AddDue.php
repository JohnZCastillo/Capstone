<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\ContentLock;
use App\exception\NotUniqueReferenceException;
use App\exception\payment\InvalidReference;
use App\exception\payment\TransactionNotFound;
use App\lib\Time;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class AddDue extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $formData = $this->getFormData();

        try {

            $month = $formData['month'];
            $amount = $formData['amount'];
            $year = $formData['dueYear'];

            $due = $this->duesService->createDue(Time::startMonth($month));
            $due->setAmount($amount);
            $due->setMonth(Time::startMonth($month));

            $this->duesService->save($due);

            $dues = $this->duesService->getMonthlyDues((int) $year);

            return $this->respondWithData($dues);

        } catch (Exception $exception) {
            return $this->respondWithData(['message' => 'error occurred'],400);
        }

    }
}