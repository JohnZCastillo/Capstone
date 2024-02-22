<?php

namespace App\controller\api\issue;

use App\controller\admin\AdminAction;
use App\lib\Image;
use App\model\IssuesMessages;
use Psr\Http\Message\ResponseInterface as Response;

class IssueImageUpload extends AdminAction
{
    protected function action(): Response
    {
        try {

            $issue = $this->issuesService->findById($this->args['id']);

            $uploadPath = './uploads/';

            $imageName = Image::store($uploadPath, $_FILES['image']);

            $file = '/uploads/' . $imageName;

            $message = new IssuesMessages();
            $message->setMessage($file);
            $message->setIssue($issue);
            $message->setImage(true);
            $message->setUser($this->getLoginUser());

            $this->issueMessageService->save($message);

            return $this->respondWithData(['message' => $message->toArray() ]);

        }catch (\Exception $exception){
            return $this->respondWithData(['message' => $exception->getMessage()],400);

        }

    }
}