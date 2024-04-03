<?php

declare(strict_types=1);

namespace App\controller\admin\project;

use App\controller\admin\AdminAction;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class ProjectType extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        try {

            $projectId = $this->args['project'];
            $typeId= $this->args['type'];


            $project = $this->projectService->getProjectById($projectId);

            switch ($typeId){
                case '0':
                    $project->setType(\App\model\enum\ProjectType::ACTIVE);
                    break;
                case '1':
                    $project->setType(\App\model\enum\ProjectType::ARCHIVE);
                    break;
            }

            $this->projectService->saveProject($project);

        } catch (Exception $exception) {
            $this->addErrorMessage($exception->getMessage());
        }

        return $this->redirect('/admin/project');
    }
}