<?php

namespace App\controller\api\area;

use App\controller\admin\AdminAction;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;

class FindLot extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {

        try {

            $formData = $this->getFormData();

            $block = $formData['block'];

            $data = $this->areaService->getLot($block);

            return $this->respondWithData($data);

        } catch (Exception $e) {
            return $this->respondWithData(['message' => 'An Internal Error Occurred'], 500);
        }
    }
}