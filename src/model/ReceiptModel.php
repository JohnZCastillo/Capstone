<?php

namespace App\model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reciept reference is unique. 
 */

#[ORM\Entity]
#[ORM\Table(name: 'receipt')]
class ReceiptModel {

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'string', nullable: true,)]
    private $path;

    #[ORM\Column(type: 'string' , nullable: true,)]
    private ?string $referenceNumber = null;

    #[ORM\Column(type: 'text' , nullable: true,)]
    private ?string $cor = null;

    #[ORM\Column(type: 'float', options: ['default'=> 0])]
    private float $amountSent;

    #[ORM\ManyToOne(targetEntity: TransactionModel::class, inversedBy: 'receipts')]
    private ?TransactionModel $transaction = null;

    public function __construct()
    {
        $this->amountSent = 0;
    }

    public function getCor(): ?string
    {
        return $this->cor;
    }

    public function setCor(?string $cor): void
    {
        $this->cor = $cor;
    }

    public function getAmountSent(): float
    {
        return $this->amountSent;
    }

    public function setAmountSent(float $amountSent): void
    {
        $this->amountSent = $amountSent;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

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
