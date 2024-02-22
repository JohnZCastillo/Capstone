<?php

namespace App\model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payment_settings')]
class PaymentModel {

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'string')]
    private $qr;

    #[ORM\Column(type: 'string')]
    private $accountName;

    #[ORM\Column(type: 'string')]
    private $accountNumber;

    #[ORM\Column(type: 'date')]
    private $start;

    

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
     * Get the value of qr
     */ 
    public function getQr()
    {
        return $this->qr;
    }

    /**
     * Set the value of qr
     *
     * @return  self
     */ 
    public function setQr($qr)
    {
        $this->qr = $qr;

        return $this;
    }

    /**
     * Get the value of accountName
     */ 
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * Set the value of accountName
     *
     * @return  self
     */ 
    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    /**
     * Get the value of accountNumber
     */ 
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * Set the value of accountNumber
     *
     * @return  self
     */ 
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * Get the value of start
     */ 
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set the value of start
     *
     * @return  self
     */ 
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

}