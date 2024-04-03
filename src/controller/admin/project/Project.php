<?php

declare(strict_types=1);

namespace App\controller\admin\project;

use App\controller\admin\AdminAction;
use App\exception\InvalidInput;
use App\lib\Time;
use App\model\enum\LogsTag;
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

            return $this->view('admin/pages/project.html', []);

        } catch (Exception $exception) {
            return $this->respondWithData(['message' => $exception->getMessage()],500);
        }

    }
}