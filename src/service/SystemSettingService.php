<?php

namespace App\service;

use App\model\SystemSettings;

class SystemSettingService extends Service
{

    public function save(SystemSettings $dues)
    {
        $this->entityManager->persist($dues);
        $this->entityManager->flush($dues);
    }

    public function findById($id = 1): SystemSettings
    {
        $em = $this->entityManager;
        $dues = $em->find(SystemSettings::class, $id);

        if (!isset($dues)) {
            return new SystemSettings();
        }

        return $dues;
    }

}