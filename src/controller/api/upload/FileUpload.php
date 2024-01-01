<?php

namespace App\controller\api\upload;

use App\controller\admin\AdminAction;
use App\lib\Image;
use Psr\Http\Message\ResponseInterface as Response;

class FileUpload extends AdminAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        try {

            $uploadPath = './uploads/';

            $imageName = Image::store($uploadPath, $_FILES['image']);

            return $this->respondWithData(['path' => '/uploads/' . $imageName]);

        }catch (\Exception $exception){
            return $this->respondWithData(['message' => $exception->getMessage()],400);

        }

    }
}