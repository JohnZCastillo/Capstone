<?php

namespace App\service;

use App\model\DuesModel;
use App\model\PrivilegesModel;
use App\model\UserModel;
use Doctrine\ORM\Exception\NotSupported;

class PriviligesService extends Service {


    public function save(PrivilegesModel $priviliges)
    {
        $this->entityManager->persist($priviliges);
        $this->entityManager->flush($priviliges);
    }


}
