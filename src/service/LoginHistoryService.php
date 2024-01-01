<?php

namespace App\service;

use App\exception\users\LoginHistoryNotFound;
use App\model\LoginHistoryModel;
use App\model\UserModel;
use DateTime;

class LoginHistoryService extends Service
{

    public function save(LoginHistoryModel $log): void
    {
        $this->entityManager->persist($log);
        $this->entityManager->flush($log);
    }

    public function addLoginLog(LoginHistoryModel $model): void
    {
        $this->entityManager->persist($model);
        $this->entityManager->flush($model);
    }

    /**
     * @throws LoginHistoryNotFound
     */
    public function getBySession(string $session): LoginHistoryModel|null
    {

        $em = $this->entityManager;

        $loginSession = $em->getRepository(LoginHistoryModel::class)
            ->findOneBy(['session' => $session]);

        if (!isset($loginSession)) {
            throw  new LoginHistoryNotFound("Missing Login history with session of $session");
        }

        return $loginSession;
    }

    public function isSessionActive(string $session): bool
    {

        $em = $this->entityManager;

        $loginHistory = $em->getRepository(LoginHistoryModel::class)
            ->findOneBy(['session' => $session]);

        if ($loginHistory == null) {
            return false;
        }

        return $loginHistory->isActive();
    }

    public function addLogoutLog(): void
    {

        $em = $this->entityManager;

        $currentSession = session_id();

        $loginHistoryModel = $em->getRepository(LoginHistoryModel::class)->findOneBy(array('session' => $currentSession));

        $logoutTime = new DateTime();; // Current date and time
        $loginHistoryModel->setLogoutDate($logoutTime);

        $this->entityManager->persist($loginHistoryModel);
        $this->entityManager->flush($loginHistoryModel);
    }

    public function getLogs(UserModel $model): array
    {

        $em = $this->entityManager;

        $logs = $em->getRepository(LoginHistoryModel::class)->findBy(['user' => $model], ['loginDate' => "desc"], 5);

        return $logs;
    }
}