<?php

declare(strict_types=1);

namespace App\controller\admin\project;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\lib\Time;
use App\model\budget\ProjectModel;
use App\model\enum\LogsTag;
use App\model\enum\ProjectStatus;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class NewProject extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        try {

            $content = $this->getFormData();

            $project = new ProjectModel();
            $project->setTitle($content['title']);
            $project->setStatus(ProjectStatus::ONGOING);

            $this->projectService->saveProject($project);

            return  $this->redirect('/admin/project');

        } catch (Exception $exception) {
            return $this->respondWithData(['message' => $exception->getMessage()],500);
        }

    }
}