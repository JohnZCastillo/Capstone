<?php

namespace App\service;

use App\model\UserModel;
use Doctrine\ORM\EntityManager;

class Service{
    
    protected $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

}
