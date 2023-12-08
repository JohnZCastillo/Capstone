<?php

namespace App\model\budget;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'income')]
class IncomeModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'string')]
    private $title;

    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\ManyToOne(targetEntity: FundSourceModel::class)]
    private ?FundSourceModel $source = null;

    #[ORM\ManyToOne(targetEntity: FundModel::class, inversedBy: 'incomes')]
    private ?FundModel $fund = null;

    #[ORM\Column(type: 'date')]
    private $createdAt;

    #[ORM\Column(type: 'date', nullable: true)]
    private $updatedAt;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
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
     * @return IncomeModel
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
     * @return IncomeModel
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * @return IncomeModel
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function getSource(): ?FundSourceModel
    {
        return $this->source;
    }

    public function setSource(?FundSourceModel $source): IncomeModel
    {
        $this->source = $source;
        return $this;
    }

    public function getFund(): ?FundModel
    {
        return $this->fund;
    }

    public function setFund(?FundModel $fund): IncomeModel
    {
        $this->fund = $fund;
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
     * @return IncomeModel
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
     * @return IncomeModel
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

}