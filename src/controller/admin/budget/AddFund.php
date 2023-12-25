<?php

namespace App\controller\admin\budget;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\model\budget\FundModel;
use DateTime;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class AddFund extends AdminAction
{

    protected function action(): Response
    {

        $content = $this->getFormData();

        try {

            if(!v::alnum()->notEmpty()->validate($content['title'])){
                throw new InvalidInput('Invalid Title');
            }

            $fund = new FundModel();
            $fund->setTitle($content['title']);
            $this->fundService->save($fund);

        }catch (InvalidInput $invalidInput){
            $this->addErrorMessage($invalidInput->getMessage());
        }catch (Exception $exception){
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/admin/budget");

    }
}