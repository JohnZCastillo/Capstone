<?php

namespace App\service;

use App\model\IssuesMessages;

class IssueMessageService extends Service {

    public function save(IssuesMessages $message): void
    {
        $this->entityManager->persist($message);
        $this->entityManager->flush($message);
    }

}