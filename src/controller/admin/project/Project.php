<?php

declare(strict_types=1);

namespace App\controller\admin\project;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\lib\Time;
use App\model\enum\LogsTag;
use App\model\enum\ProjectStatus;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class Project extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        try {

            $content = $this->getQueryParams();

            $type = $content['type'] ?? 'active';

            switch ($type){
                case 'active':
                    $type = \App\model\enum\ProjectType::ACTIVE;
                    break;
                case 'archive':
                    $type = \App\model\enum\ProjectType::ARCHIVE;
                    break;
                default :
                    $type = \App\model\enum\ProjectType::ACTIVE;
            }

            $projects = $this->projectService->getProjects($type);

            $totalCompleted = $this->projectService->count(ProjectStatus::COMPLETED);
            $totalOngoing = $this->projectService->count(ProjectStatus::ONGOING);
            $totalCancelled = $this->projectService->count(ProjectStatus::CANCELLED);

            return $this->view('admin/pages/project.html',
                [
                    'projects' => $projects,
                    'totalCompleted' => $totalCompleted,
                    'totalOngoing' => $totalOngoing,
                    'totalCancelled' => $totalCancelled,
                    'type' => $type
                ]
            );

        } catch (Exception $exception) {
            return $this->respondWithData(['message' => $exception->getMessage()],500);
        }

    }
}