<?php

namespace App\model;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'transaction')]
class TransactionModel {

    private $statuses = [
        "PENDING",
        "APPROVED",
        "REJECTED",
    ];  

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\ManyToOne(targetEntity: UserModel::class, inversedBy: 'transactions',)]
    private ?UserModel $user = null;

    #[ORM\ManyToOne(targetEntity: UserModel::class, )]
    #[ORM\JoinColumn(nullable: true )]
    private ?UserModel $processBy = null;

    #[ORM\Column(type: 'float')]
    private float $amount;

    #[ORM\Column(type: 'datetime')]
    private ?DateTime $fromMonth;

    #[ORM\Column(type: 'datetime')]
    private ?DateTime $toMonth;

    #[ORM\Column(type: 'string')]
    private string $status = 'PENDING';

    #[ORM\Column(type: 'string' , options: ['default' => 'gcash'])]
    private string $paymentMethod;

    #[ORM\Column(type: 'datetime')]
    private ?DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'transaction', targetEntity: ReceiptModel::class)]
    private Collection|array $receipts;

    #[ORM\OneToMany(mappedBy: 'transaction', targetEntity: TransactionLogsModel::class)]
    private Collection|array $logs;

    public function __construct()
    {
        $this->updatedAt = new DateTime();
        $this->createdAt = new DateTime();
        $this->paymentMethod = 'gcash';
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function setStatuses(array $statuses): void
    {
        $this->statuses = $statuses;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getUser(): ?UserModel
    {
        return $this->user;
    }

    public function setUser(?UserModel $user): void
    {
        $this->user = $user;
    }

    public function getProcessBy(): ?UserModel
    {
        return $this->processBy;
    }

    public function setProcessBy(?UserModel $processBy): void
    {
        $this->processBy = $processBy;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getFromMonth(): ?DateTime
    {
        return $this->fromMonth;
    }

    public function setFromMonth(?DateTime $fromMonth): void
    {
        $this->fromMonth = $fromMonth;
    }

    public function getToMonth(): ?DateTime
    {
        return $this->toMonth;
    }

    public function setToMonth(?DateTime $toMonth): void
    {
        $this->toMonth = $toMonth;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getReceipts(): Collection|array
    {
        return $this->receipts;
    }

    public function setReceipts(Collection|array $receipts): void
    {
        $this->receipts = $receipts;
    }

    public function getLogs(): Collection|array
    {
        return $this->logs;
    }

    public function setLogs(Collection|array $logs): void
    {
        $this->logs = $logs;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

}