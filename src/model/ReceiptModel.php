<?php

namespace App\model;

use App\lib\Time;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'receipt')]
class ReceiptModel {

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'string')]
    private $path;

    #[ORM\Column(type: 'string')]
    private $referenceNumber;

    #[ORM\ManyToOne(targetEntity: TransactionModel::class, inversedBy: 'receipts')]
    private ?TransactionModel $transaction = null;

}
