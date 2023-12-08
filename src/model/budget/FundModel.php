<?php

namespace App\model\budget;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'fund')]
class FundModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'string')]
    private $title;

    #[ORM\OneToMany(mappedBy: 'fund', targetEntity: IncomeModel::class)]
    private Collection|array $incomes;

    #[ORM\OneToMany(mappedBy: 'fund', targetEntity: ExpenseModel::class)]
    private Collection|array $expenses;

    #[ORM\ManyToMany(targetEntity: FundModel::class, mappedBy: 'mergedFunds')]
    private Collection $mergedFunds;

    #[ORM\Column(type: 'date')]
    private $createdAt;

    #[ORM\Column(type: 'boolean')]
    private $isArchived;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $mainFund;

    public function __construct()
    {
        $this->incomes = new ArrayCollection();
        $this->expenses = new ArrayCollection();
        $this->mergedFunds = new ArrayCollection();
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
     * @return FundModel
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getIncomes(): Collection|array
    {
        return $this->incomes;
    }

    public function setIncomes(Collection|array $incomes): FundModel
    {
        $this->incomes = $incomes;
        return $this;
    }

    public function getExpenses(): Collection|array
    {
        return $this->expenses;
    }

    public function setExpenses(Collection|array $expenses): FundModel
    {
        $this->expenses = $expenses;
        return $this;
    }

    public function getMergedFunds(): Collection
    {
        return $this->mergedFunds;
    }

    public function setMergedFunds(Collection $mergedFunds): FundModel
    {
        $this->mergedFunds = $mergedFunds;
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
     * @return FundModel
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsArchived()
    {
        return $this->isArchived;
    }

    /**
     * @param mixed $isArchived
     * @return FundModel
     */
    public function setIsArchived($isArchived)
    {
        $this->isArchived = $isArchived;
        return $this;
    }

    public function computeTotal():float{

        $total = 0;

        foreach ($this->incomes as $income){
            $total += $income->getAmount();
        }

        foreach ($this->expenses as $expense){
            $total -= $expense->getAmount();
        }

        return  0;
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
     * @return FundModel
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isMainFund()
    {
        return $this->mainFund;
    }

    /**
     * @param mixed $mainFund
     * @return FundModel
     */
    public function setMainFund($mainFund)
    {
        $this->mainFund = $mainFund;
        return $this;
    }


}