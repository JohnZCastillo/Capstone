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

    /**
     * Get the value of amount
     */ 
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set the value of amount
     *
     * @return  self
     */ 
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of receiptId
     */ 
    public function getReceiptId()
    {
        return $this->receiptId;
    }

    /**
     * Set the value of receiptId
     *
     * @return  self
     */ 
    public function setReceiptId($receiptId)
    {
        $this->receiptId = $receiptId;

        return $this;
    }

    /**
     * Get the value of forMonth
     */ 
    public function getForMonth()
    {
        return $this->forMonth;
    }

    /**
     * Set the value of forMonth
     *
     * @return  self
     */ 
    public function setForMonth($forMonth)
    {
        $this->forMonth = $forMonth;

        return $this;
    }

    /**
     * Get the value of toMonth
     */ 
    public function getToMonth()
    {
        return $this->toMonth;
    }

    /**
     * Set the value of toMonth
     *
     * @return  self
     */ 
    public function setToMonth($toMonth)
    {
        $this->toMonth = $toMonth;

        return $this;
    }

    /**
     * Get the value of createdAt
     */ 
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */ 
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of user
     */ 
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */ 
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
