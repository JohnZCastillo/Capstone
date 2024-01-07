<?php

declare(strict_types=1);

namespace App\controller\admin\payments;

use App\controller\admin\AdminAction;
use App\exception\InvalidFile;
use App\exception\payment\PaymentNotFound;
use App\lib\Image;
use App\lib\Time;
use App\model\enum\LogsTag;
use chillerlan\QRCode\QRCode;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

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

                $storedFile = Image::store($path, $_FILES['qr']);

                if (!v::image()->validate($path . $storedFile)) {
                    throw  new InvalidFile('Unsupported File');
                }

                try {
                    $result = (new QRCode)->readFromFile($path . $storedFile);
                } catch (Exception $exception) {
                    throw new InvalidFile('Qr not detected');
                }

                $paymentModel->setQr($storedFile);
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
        } catch (PaymentNotFound $paymentNotFound) {
            $this->addErrorMessage('Payment Not Found!');
        } catch (Exception $exception) {
            $this->addErrorMessage('An  Internal Error Has Occurred, pleas check logs');
        }

        return $this->redirect("/admin/payments");
    }
}