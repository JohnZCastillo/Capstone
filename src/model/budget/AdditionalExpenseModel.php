<?php

namespace App\model\budget;

use App\model\enum\BudgetStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'additional_expense')]
class AdditionalExpenseModel{

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

    #[ORM\Column(type: BudgetStatus::class)]
    private $status;

    #[ORM\ManyToOne(targetEntity: ExpenseModel::class, inversedBy: 'additional')]
    private ?ExpenseModel $expense = null;

    #[ORM\Column(type: 'date')]
    private $createdAt;

    #[ORM\Column(type: 'date')]
    private $updatedAt;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return AdditionalExpenseModel
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
     * @return AdditionalExpenseModel
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
     * @return AdditionalExpenseModel
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
     * @return AdditionalExpenseModel
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
     * @return AdditionalExpenseModel
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getExpense(): ?ExpenseModel
    {
        return $this->expense;
    }

    public function setExpense(?ExpenseModel $expense): AdditionalExpenseModel
    {
        $this->expense = $expense;
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
     * @return AdditionalExpenseModel
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
     * @return AdditionalExpenseModel
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

}