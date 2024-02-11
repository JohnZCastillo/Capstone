<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\image\ImageUploadException;
use App\exception\InvalidFile;
use App\exception\payment\PaymentNotFound;
use App\lib\ImageUploadManager;
use App\lib\Time;
use App\model\enum\LogsTag;
use App\model\PaymentModel;
use Exception;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Psr\Http\Message\ResponseInterface as Response;

class PaymentSettings extends AdminAction
{
    /**
     * Update payment settings action
     *
     * This method handles the updating of payment settings based on the form data received.
     * It validates the form data, updates the payment information in the database,
     * and handles any errors that may occur during the process.
     *
     * @return Response
     */
    protected function action(): Response
    {

        $formData = $this->getFormData();

        try {

            $id = $formData['id'];
            $name = $formData['name'];
            $number = $formData['number'];
            $start = $formData['start'];

            try {
                $paymentModel = $this->paymentService->findById((int)$id);
            }catch (PaymentNotFound $paymentNotFound){
                $paymentModel = new PaymentModel();
            }

            $uploadPath = './uploads/';
            $fileName = 'qr';

            //only update qr image if a new qr is uploaded
            try {
                $qr = ImageUploadManager::upload($fileName,$uploadPath);
                $paymentModel->setQr($qr);
            }catch (ImageUploadException $exception){

                $image = $paymentModel->getQr();

                if(empty($image)){
                    throw  $exception;
                }
            }

            $paymentModel->setAccountName($name);
            $paymentModel->setAccountNumber($number);
            $paymentModel->setStart(Time::startMonth($start));

            $this->paymentService->save($paymentModel);

            $this->addActionLog("Payment settings was update", 'Payment Settings');

            $this->addSuccessMessage('Payment Settings Updated!');

            $this->addActionLog('Payment Settings Updated!', LogsTag::paymentSettings());

        } catch (InvalidFile $invalidFile) {
            $this->addErrorMessage($invalidFile->getMessage());
        } catch ( ImageUploadException $imageUploadException){
            $this->addErrorMessage('Qr is missing');
        }catch (Exception $exception) {
            $this->addErrorMessage('An  Internal Error Has Occurred, pleas check logs');
        }

        return $this->redirect("/admin/payments");
    }
}