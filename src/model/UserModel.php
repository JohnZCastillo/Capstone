<?php

namespace App\model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(name: "email", columns: ["email"])]
class UserModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'string')]
    private string $name; 

    #[ORM\Column(type: 'string')]
    private string $block; 

    #[ORM\Column(type: 'string')]
    private string $lot; 

    #[ORM\Column(type: 'string')]
    private string $email; 

    #[ORM\Column(type: 'string')]
    private string $password; 

    #[ORM\OneToMany(targetEntity: TransactionModel::class, mappedBy: 'transaction')]
    private Collection|array $transactions;

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

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
     * Get the value of block
     */ 
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * Set the value of block
     *
     * @return  self
     */ 
    public function setBlock($block)
    {
        $this->block = $block;

        return $this;
    }

    /**
     * Get the value of lot
     */ 
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set the value of lot
     *
     * @return  self
     */ 
    public function setLot($lot)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of password
     */ 
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */ 
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }
}