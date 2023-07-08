<?php

namespace App\service;

use App\model\UserModel;
use Doctrine\ORM\EntityManager;

class Service{
    
    protected $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function save(UserModel $user){
        $this->entityManager->persist($user);    
        $this->entityManager->flush($user);
    }

}
