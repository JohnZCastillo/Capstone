<?php

namespace App\controller\admin\issues;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\exception\issue\IssueNotFoundException;
use App\model\enum\LogsTag;
use Doctrine\DBAL\Driver\PDO\Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class MakeAction extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        $id = $this->getFormData()['id'];


        try {

            $issue = $this->issuesService->findById($id);

            $action = $this->getFormData()['action'];
            $status = $this->getFormData()['status'];

            if(!v::stringType()->notEmpty()->validate($action)){
                throw new InvalidInput('Message cannot be empty');
            }

            $issue->setAction($action);
            $issue->setStatus($status);

            $this->issuesService->save($issue);

            $this->addActionLog("Issue with id of $id was $status ",LogsTag::issue());

        } catch (IssueNotFoundException $issueNotFoundException) {
            $this->addErrorMessage($issueNotFoundException->getMessage());
            return $this->redirect('/admin/issues');
        } catch (InvalidInput $invalidInput) {
            $this->addErrorMessage($invalidInput->getMessage());
        } catch (\Exception $exception) {
            $this->addErrorMessage('An Internal Error Occurred');
        }

        return $this->redirect("/admin/issue/$id");
    }
}