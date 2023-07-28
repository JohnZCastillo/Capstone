<?php

namespace App\model;

use App\lib\Time;
use Doctrine\ORM\Mapping as ORM;

/**
 * Reciept reference is unique. 
 */

#[ORM\Entity]
#[ORM\Table(name: 'receipt')]
#[ORM\UniqueConstraint(name: "reference", columns: ["referenceNumber"])]
class ReceiptModel {

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'string')]
    private $path;

    #[ORM\Column(type: 'string' , nullable: true,)]
    private ?string $referenceNumber = null;

    #[ORM\ManyToOne(targetEntity: TransactionModel::class, inversedBy: 'receipts')]
    private ?TransactionModel $transaction = null;


    /**
     * Get the value of path
     */ 
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * @return  self
     */ 
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the value of referenceNumber
     */ 
    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    /**
     * Set the value of referenceNumber
     *
     * @return  self
     */ 
    public function setReferenceNumber($referenceNumber)
    {
        $this->referenceNumber = $referenceNumber;

        return $this;
    }

    /**
     * Get the value of transaction
     */ 
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set the value of transaction
     *
     * @return  self
     */ 
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }
}
