<?php

namespace App\controller\admin\overview;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\lib\Image;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class UpdateOverview extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try {
            $content = $this->getFormData();

            $path = './resources/overview/';

            $aboutImage = $_FILES['aboutImage'];
            $heroImage = $_FILES['heroImage'];

            $overview = $this->overviewService->getOverview();

            $aboutDescription = $content['aboutDescription'];
            $heroDescription = $content['heroDescription'];

            if (!v::alnum(' ')->notEmpty()->validate($aboutDescription)) {
                throw new InvalidInput('Invalid content for about description');
            }

            if (!v::alnum(' ')->notEmpty()->validate($heroDescription)) {
                throw new InvalidInput('Invalid content for hero description');
            }

            $overview->setAboutDescription($aboutDescription);
            $overview->setHeroDescription($heroDescription);

            if ($aboutImage['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageName = Image::store($path, $aboutImage);
                $overview->setAboutImg(str_replace('.', '', $path) . $imageName);
            }

            if ($heroImage['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageName = Image::store($path, $heroImage);
                $overview->setAboutImg(str_replace('.', '', $path) . $imageName);
            }

            $this->overviewService->saveOverview($overview);

            $this->addSuccessMessage('Overview Updated');

        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (\Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect('/admin/overview');
    }
}