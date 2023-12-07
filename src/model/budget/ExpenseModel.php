<?php

namespace App\model\budget;

use App\model\enum\BudgetStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'expense')]
class ExpenseModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'string')]
    private $title;

    #[ORM\Column(type: 'string')]
    private $purpose;

    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\ManyToOne(targetEntity: FundSourceModel::class, inversedBy: 'expenses')]
    private ?FundModel $fund = null;

    #[ORM\Column(type: BudgetStatus::class)]
    private $status;

    #[ORM\OneToMany(mappedBy: 'expense', targetEntity: AdditionalExpenseModel::class)]
    private Collection|array $additional;

    #[ORM\Column(type: 'date')]
    private $createdAt;

    #[ORM\Column(type: 'date')]
    private $updatedAt;

    public function __construct()
    {
        $this->additional = new ArrayCollection();
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ExpenseModel
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return ExpenseModel
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * @param mixed $purpose
     * @return ExpenseModel
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     * @return ExpenseModel
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function getFund(): ?FundModel
    {
        return $this->fund;
    }

    public function setFund(?FundModel $fund): ExpenseModel
    {
        $this->fund = $fund;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return ExpenseModel
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getAdditional(): Collection|array
    {
        return $this->additional;
    }

    public function setAdditional(Collection|array $additional): ExpenseModel
    {
        $this->additional = $additional;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return ExpenseModel
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return ExpenseModel
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

}