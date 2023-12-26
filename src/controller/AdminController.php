<?php

namespace App\controller;

use App\lib\Filter;
use App\lib\Helper;
use App\lib\Image;
use App\lib\Login;
use App\lib\Time;
use App\model\AnnouncementModel;
use App\model\budget\BillModel;
use App\model\budget\ExpenseModel;
use App\model\budget\FundModel;
use App\model\budget\IncomeModel;
use App\model\enum\AnnouncementStatus;
use App\model\enum\BudgetStatus;
use App\model\enum\UserRole;
use App\model\LogsModel;
use App\model\overview\Staff;
use App\model\PaymentModel;
use DateTime;
use Exception;
use Respect\Validation\Validator as V;
use Slim\Views\Twig;

class AdminController extends Controller
{
    public function overview($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $overview = $this->overviewService->getOverview();

        $staffs = $this->overviewService->getAllStaff();

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


        return $twig->render($response, 'admin/pages/overview.html', [
            "overview" => $overview,
            "staffs" => $staffs,
            "org" => $orgStaff,
        ]);

    }

    public function landingPage($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $overview = $this->overviewService->getOverview();

        $staffs = $this->overviewService->getAllStaff();

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


        return $twig->render($response, 'homepage.html', [
            "overview" => $overview,
            "staffs" => $staffs,
            "org" => $orgStaff,
        ]);

    }

    public function updateOverview($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $path = './resources/overview/';

        $aboutImage = $_FILES['aboutImage'];
        $heroImage = $_FILES['heroImage'];

        $overview = $this->overviewService->getOverview();

        $content = $request->getParsedBody();

        $overview->setAboutDescription($content['aboutDescription']);
        $overview->setHeroDescription($content['heroDescription']);

        if ($aboutImage['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageName = Image::store($path, $aboutImage);
            $overview->setAboutImg(str_replace('.', '', $path) . $imageName);
        }

        if ($heroImage['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageName = Image::store($path, $heroImage);
            $overview->setAboutImg(str_replace('.', '', $path) . $imageName);
        }

        $this->overviewService->saveOverview($overview);

        return $response
            ->withHeader('Location', "/admin/overview")
            ->withStatus(302);

    }

    public function addStaff($request, $response, $args)
    {

        $path = './resources/staff/';

        $twig = Twig::fromRequest($request);

        $image = $_FILES['image'];

        $content = $request->getParsedBody();

        try{
            $superior = $this->overviewService->getStaffById($content['superior']);

            $staff = new Staff();
            $staff->setName($content['name']);
            $staff->setPosition($content['position']);

            if (isset($superior)) {
                $staff->setSuperior($superior);
            }

            if ($image['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageName = Image::store($path, $image);
                $staff->setImg(str_replace('.', '', $path) . $imageName);
            }

            $this->overviewService->saveStaff($staff);
        }catch (Exception $e){

            $message = $e->getMessage();

            if($e->getCode() == 1062){
                $message = 'Name is already define';
            }

            $this->flashMessages->addMessage('errorMessage',$message);
        }


        return $response
            ->withHeader('Location', "/admin/overview")
            ->withStatus(302);

    }

    public function removeStaff($request, $response, $args)
    {

        $twig = Twig::fromRequest($request);

        $content = $request->getParsedBody();

        try{

            $staff = $this->overviewService->getStaffByName($content['name']);

            if(isset($staff)){
                $this->overviewService->deleteStaff($staff);
            }

        }catch (Exception $e){

            $message = $e->getMessage();

            if($e->getCode() == 1451){
                $message = 'Cannot Remove Staff, please remove lower staff first';
            }

            $this->flashMessages->addMessage('errorMessage',$message);
        }

        return $response
            ->withHeader('Location', "/admin/overview")
            ->withStatus(302);

    }

}