<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
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

            if(!v::number()->notEmpty()->validate($amount)){
                throw  new InvalidInput('Invalid Amount');
            }

            if(!v::date('Y-m')->notEmpty()->validate($month)){
                throw  new InvalidInput('Invalid Month');
            }

            if(!v::date('Y')->notEmpty()->validate($year)){
                throw  new InvalidInput('Invalid year');
            }

            $due = $this->duesService->createDue(Time::startMonth($month));
            $due->setAmount($amount);
            $due->setMonth(Time::startMonth($month));

            $this->duesService->save($due);

            $dues = $this->duesService->getMonthlyDues((int) $year);

            return $this->respondWithData($dues);

        } catch (InvalidInput $invalidInput) {
            return $this->respondWithData(['message' => $invalidInput->getMessage()],400);
        } catch (Exception $exception) {
            return $this->respondWithData(['message' => $exception->getMessage()],500);
        }

    }
}