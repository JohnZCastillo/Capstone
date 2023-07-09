<?php

namespace App\model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'transaction')]
class TransactionModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\ManyToOne(targetEntity: UserModel::class, inversedBy: 'transactions')]
    private ?UserModel $user = null;

    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\Column(type: 'integer')]
    private $receiptId;

    #[ORM\Column(type: 'date')]
    private $forMonth;

    #[ORM\Column(type: 'date')]
    private $toMonth;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

}
