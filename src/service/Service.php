<?php

namespace App\service;

use Doctrine\ORM\EntityManager;

class Service{
    
    protected $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

}
