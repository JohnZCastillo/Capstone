<?php

namespace App\model;

use App\model\enum\IssuesStatus;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'issues')]
class IssuesModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: IssuesStatus::class)]
    private $status;
    
    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\ManyToOne(targetEntity: UserModel::class, inversedBy: 'issues',)]
    private ?UserModel $user = null;

    #[ORM\OneToOne(targetEntity: TransactionModel::class)]
    private TransactionModel|null $transaction;

    #[ORM\OneToMany(mappedBy: 'issue', targetEntity: IssuesMessages::class)]
    private Collection $messages;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUser(): ?UserModel
    {
        return $this->user;
    }

    public function setUser(?UserModel $user): void
    {
        $this->user = $user;
    }

    public function getTransaction(): ?TransactionModel
    {
        return $this->transaction;
    }

    public function setTransaction(?TransactionModel $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function setMessages(Collection $messages): void
    {
        $this->messages = $messages;
    }

}