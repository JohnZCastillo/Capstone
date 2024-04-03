<?php

declare(strict_types=1);

namespace App\controller\admin\project;

use App\controller\admin\AdminAction;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class ProjectStatus extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        try {

            $projectId = $this->args['project'];
            $statusId = $this->args['status'];


            $project = $this->projectService->getProjectById($projectId);

            switch ($statusId){
                case '0':
                    $project->setStatus(\App\model\enum\ProjectStatus::COMPLETED);
                    break;
                case '1':
                    $project->setStatus(\App\model\enum\ProjectStatus::ONGOING);
                    break;
                case '2':
                    $project->setStatus(\App\model\enum\ProjectStatus::CANCELLED);
                    break;
            }

            $this->projectService->saveProject($project);

        } catch (Exception $exception) {
            $this->addErrorMessage($exception->getMessage());
        }

        return $this->redirect('/admin/project');
    }
}