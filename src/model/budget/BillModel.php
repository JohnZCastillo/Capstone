<?php

namespace App\model\budget;

use App\model\enum\BudgetStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'bill')]
class BillModel
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\OneToOne(targetEntity: ExpenseModel::class)]
    private ExpenseModel|null $expense;

    #[ORM\Column(type: 'date')]
    private $createdAt;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $isArchived;

    #[ORM\OneToMany(mappedBy: 'bill', targetEntity: ExpenseModel::class)]
    private Collection|array $expenses;

    public function __construct()
    {
        $this->expenses = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->isArchived = false;
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
     * @return BillModel
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getExpense(): ?ExpenseModel
    {
        return $this->expense;
    }

    public function setExpense(?ExpenseModel $expense): BillModel
    {
        $this->expense = $expense;
        return $this;
    }

    public function getExpenses(): Collection|array
    {
        return $this->expenses;
    }

    public function setExpenses(Collection|array $expenses): BillModel
    {
        $this->expenses = $expenses;
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
     * @return BillModel
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isArchived()
    {
        return $this->isArchived;
    }

    /**
     * @param mixed $isArchived
     * @return BillModel
     */
    public function setIsArchived($isArchived)
    {
        $this->isArchived = $isArchived;
        return $this;
    }

}