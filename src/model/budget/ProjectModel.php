<?php

namespace App\model\budget;

use App\model\enum\ProjectStatus;
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

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectExpenseModel::class)]
    private Collection|array $expense;

    #[ORM\Column(type: 'date')]
    private $createdAt;

    #[ORM\Column(type: 'date', nullable: true)]
    private $updatedAt;

    public function __construct()
    {
        $this->expense = new ArrayCollection();
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

    public function getExpense(): Collection|array
    {
        return $this->expense;
    }

    public function setExpense(Collection|array $expense): void
    {
        $this->expense = $expense;
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


}