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
use thiagoalessio\TesseractOCR\Tests\Common\TestCase;

class UnitOverview extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $userId = $this->args['id'];
        $transactionId = $this->args['transaction'];

        $user = $this->userService->findById($userId);

        $unpaidDue = $this->transactionService->getUnpaid(
            $user,
            $this->duesService,
            $this->paymentService->findById(1),
            '2023-01-01',
            '2023-12-01'
        );

        $unpaidDue['transactionId'] =  $transactionId;

        return $this->view('admin/pages/unit-overview.html', $unpaidDue);
    }
}