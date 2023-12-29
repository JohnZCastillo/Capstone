<?php

namespace App\controller\admin\overview;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\lib\Image;
use App\model\overview\Features;
use App\model\overview\Staff;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class RemoveFeature extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try {


            $content = $this->getFormData();

            $feature = $this->overviewService->getFeatureById($content['id']);

            $featureName = $feature->getName();

            $this->overviewService->deleteFeature($feature);

            $this->addSuccessMessage("$featureName removed successfully");
        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (\Exception $exception) {
            $message = 'An Internal Error Occurred';
            $this->addErrorMessage($message);
        }

        return $this->redirect('/admin/overview#featuerSection');
    }
}