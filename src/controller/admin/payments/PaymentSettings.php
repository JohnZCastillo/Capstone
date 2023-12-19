<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\ContentLock;
use App\exception\NotUniqueReferenceException;
use App\exception\payment\InvalidReference;
use App\exception\payment\PaymentNotFound;
use App\exception\payment\TransactionNotFound;
use App\lib\Image;
use App\lib\Time;
use App\model\LogsModel;
use App\model\PaymentModel;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use Slim\Views\Twig;

class PaymentSettings extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {

        $formData = $this->getFormData();

        try {

            $id = $formData['id'];
            $name = $formData['name'];
            $number = $formData['number'];
            $start = $formData['start'];

            $paymentModel = $this->paymentService->findById($id);

            $image = $_FILES['qr'];

            if ($image['error'] !== UPLOAD_ERR_NO_FILE) {
                $path = './uploads/';
                $paymentModel->setQr(Image::store($path, $_FILES['qr']));
            }

            $paymentModel->setAccountName($name);
            $paymentModel->setAccountNumber($number);
            $paymentModel->setStart(Time::startMonth($start));

            $this->paymentService->save($paymentModel);

            $this->addActionLog("Payment settings was update", 'Payment Settings');

            $this->addSuccessMessage('Payment Settings Updated!');

        } catch (PaymentNotFound $paymentNotFound) {
            $this->addErrorMessage('Payment Not Found!');
        } catch (Exception $exception) {
            $this->addErrorMessage('An  Internal Error Has Occurred, pleas check logs');
        }

        return $this->redirect("/admin/payments");
    }
}