<?php

namespace App\model\budget;

use App\model\enum\BudgetStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'project_expense_proof')]
class ProjectExpenseProofModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'string')]
    private $image;

    #[ORM\ManyToOne(targetEntity: ProjectExpenseModel::class, inversedBy: 'proof',)]
    private ?ProjectExpenseModel $projectExpense = null;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image): void
    {
        $this->image = $image;
    }

    public function getProjectExpense(): ?ProjectExpenseModel
    {
        return $this->projectExpense;
    }

    public function setProjectExpense(?ProjectExpenseModel $projectExpense): void
    {
        $this->projectExpense = $projectExpense;
    }

}