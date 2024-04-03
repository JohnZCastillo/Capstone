<?php

namespace App\model\budget;

use App\model\enum\ProjectStatus;
use App\model\enum\ProjectType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'project')]
class ProjectModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'string')]
    private $title;

    #[ORM\Column(type: ProjectStatus::class)]
    private $status;

    #[ORM\Column(type: ProjectType::class, options: ['default' => ProjectType::ACTIVE])]
    private $type;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectExpenseModel::class)]
    private Collection|array $expenses;


    #[ORM\Column(type: 'date')]
    private $createdAt;

    #[ORM\Column(type: 'date', nullable: true)]
    private $updatedAt;

    public function __construct()
    {
        $this->expenses = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function getExpenses(): Collection|array
    {
        return $this->expenses;
    }

    public function setExpenses(Collection|array $expenses): void
    {
        $this->expenses = $expenses;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getTotalExpense(): float{

        $total = 0;

        /** @var ProjectExpenseModel $expense */
        foreach ($this->expenses as $expense) {
            $total += $expense->getAmount();
        }

        return $total;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

}