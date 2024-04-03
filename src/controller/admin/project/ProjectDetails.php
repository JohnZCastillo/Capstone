<?php

declare(strict_types=1);

namespace App\controller\admin\project;

use App\controller\admin\AdminAction;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class ProjectDetails extends AdminAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        try {

            $id = $this->args['id'];

            $project = $this->projectService->getProjectById($id);

            return $this->view('admin/pages/project-details.html',
                [
                    'project' => $project
                ]
            );

        } catch (Exception $exception) {
            return $this->respondWithData(['message' => $exception->getMessage()],500);
        }

    }
}