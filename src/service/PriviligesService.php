<?php

namespace App\service;

use App\model\PrivilegesModel;

class PriviligesService extends Service {


    public function save(PrivilegesModel $priviliges)
    {
        $this->entityManager->persist($priviliges);
        $this->entityManager->flush($priviliges);
    }


}
