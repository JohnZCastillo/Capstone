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

class YearlyDue extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $formData = $this->getFormData();

        try {

            $year = $formData['dueYear'];

            $dues = $this->duesService->getMonthlyDues((int) $year);

            return $this->respondWithData($dues);

        } catch (Exception $exception) {
            return $this->respondWithData(['message' => 'error occurred'],400);
        }

    }
}