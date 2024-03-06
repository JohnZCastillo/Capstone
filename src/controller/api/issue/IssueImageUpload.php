<?php

namespace App\controller\api\issue;

use App\controller\admin\AdminAction;
use App\lib\Image;
use App\model\enum\IssuesStatus;
use App\model\IssuesMessages;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;

class IssueImageUpload extends AdminAction
{
    protected function action(): Response
    {
        try {

            $issue = $this->issuesService->findById($this->args['id']);

            $uploadPath = './uploads/';

            $imageName = Image::store($uploadPath, $_FILES['image']);

            $file = '/uploads/' . $imageName;

            if($issue->getStatus() == IssuesStatus::PENDING){

                $message = new IssuesMessages();
                $message->setMessage($file);
                $message->setIssue($issue);
                $message->setUser($this->getLoginUser());

                if(v::image()->validate($uploadPath . $imageName)){
                    $message->setImage(true);
                }else{
                    $message->setFile(true);
                }

                $this->issueMessageService->save($message);

                return $this->respondWithData(['message' => $message->toArray()]);

            }

            return $this->respondWithData(['message' => [] ]);

        }catch (\Exception $exception){
            return $this->respondWithData(['message' => $exception->getMessage()],400);
        }

    }
}