<?php

namespace App\service;

use App\model\LoginHistoryModel;
use App\model\UserModel;
use DateTime;

class LoginHistoryService  extends Service {

    public function addLoginLog(LoginHistoryModel $model): void{
        $this->entityManager->persist($model);
        $this->entityManager->flush($model);
    }

    public function addLogoutLog(): void{

        $em = $this->entityManager;

        $currentSession = session_id();

        $loginHistoryModel =  $em->getRepository(LoginHistoryModel::class)->findOneBy(array('session' => $currentSession));

        $logoutTime = new DateTime();; // Current date and time
        $loginHistoryModel->setLogoutDate($logoutTime);

        $this->entityManager->persist($loginHistoryModel);
        $this->entityManager->flush($loginHistoryModel);
    }

    public function  getLogs(UserModel $model): array{

        $em = $this->entityManager;

        $logs = $em->getRepository(LoginHistoryModel::class)->findBy(['user' => $model],['loginDate'=>"desc"],5);

        return $logs;
    }
}