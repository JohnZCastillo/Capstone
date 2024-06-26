<?php

namespace App\model\budget;

use App\model\enum\BudgetStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'project_expense')]
class ProjectExpenseModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'string')]
    private $title;


    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\OneToMany(mappedBy: 'projectExpense', targetEntity: ProjectExpenseProofModel::class)]
    private Collection|array $proofs;

    #[ORM\ManyToOne(targetEntity: ProjectModel::class, inversedBy: 'expenses')]
    private ?ProjectModel $project;

    public function __construct()
    {
        $this->proofs = new ArrayCollection();
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

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    public function getProofs(): Collection|array
    {
        return $this->proofs;
    }

    public function setProofs(Collection|array $proofs): void
    {
        $this->proofs = $proofs;
    }

    public function getProject(): ?ProjectModel
    {
        return $this->project;
    }

    public function setProject(?ProjectModel $project): void
    {
        $this->project = $project;
    }

}