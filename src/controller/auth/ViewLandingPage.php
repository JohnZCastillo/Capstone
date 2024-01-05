<?php

namespace App\controller\auth;

use App\controller\admin\AdminAction;
use Psr\Http\Message\ResponseInterface as Response;

class ViewLandingPage extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $overview = $this->overviewService->getOverview();

        $staffs = $this->overviewService->getAllStaff();

        $features = $this->overviewService->getAllFeatures();

        $orgStaff = [];

        foreach ($staffs as $staff) {

            $img = $staff->getImg();
            $name = $staff->getName();
            $position = $staff->getPosition();

            $superior = $staff->getSuperior();
            $superiorName = '';

            if (isset($superior)) {
                $superiorName = $superior->getName();
            }

            $orgStaff[] = [
                'name' => $staff->getName(),
                'position' => $position,
                'img' => $img,
                'superior' => $superiorName,
            ];

        }

        return $this->view('homepage.html',[
            "overview" => $overview,
            "staffs" => $staffs,
            "org" => $orgStaff,
            "features" => $features,
        ]);
    }
}