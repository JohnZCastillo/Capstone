<?php

namespace App\service;

use App\model\overview\Features;
use App\model\overview\Overview;
use App\model\overview\Staff;

class OverviewService extends Service
{

    public function saveOverview(Overview $overview)
    {
        $this->entityManager->persist($overview);
        $this->entityManager->flush($overview);
    }

    public function saveStaff(Staff $staff)
    {
        $this->entityManager->persist($staff);
        $this->entityManager->flush($staff);
    }

    public function saveFeature(Features $features)
    {
        $this->entityManager->persist($features);
        $this->entityManager->flush($features);
    }

    public function deleteStaff(Staff $staff)
    {
        $this->entityManager->remove($staff);
        $this->entityManager->flush($staff);
    }


    public function deleteFeature(Features $features)
    {
        $this->entityManager->remove($features);
        $this->entityManager->flush($features);
    }

    public function getAllFeatures(): array
    {
        $em = $this->entityManager;

        return $em->getRepository(Features::class)
            ->findAll();
    }

    public function getFeatureById($id): Features|null
    {
        $em = $this->entityManager;

        return $em->getRepository(Features::class)
            ->findOneBy(['id' => $id]);
    }

    public function getAllStaff(): array
    {
        $em = $this->entityManager;

        return $em->getRepository(Staff::class)
            ->findAll();
    }

    public function getStaffById($id): Staff|null
    {
        $em = $this->entityManager;

        return $em->getRepository(Staff::class)
            ->findOneBy(['id' => $id]);
    }

    public function getStaffByName($name): Staff|null
    {
        $em = $this->entityManager;

        return $em->getRepository(Staff::class)
            ->findOneBy(['name' => $name]);
    }


    public function getOverview(): Overview
    {
        $em = $this->entityManager;

        $overview = $em->getRepository(Overview::class)
            ->findOneBy(['id' => 1]);

        if (!isset($overview)) {
            $overview = new Overview();
            $overview->setAboutDescription('');
            $overview->setHeroDescription('');
            $this->saveOverview($overview);
        }

        return $overview;
    }
}
