<?php

namespace App\model\overview;

use App\lib\Encryptor;
use App\model\AnnouncementModel;
use App\model\enum\UserRole;
use App\model\IssuesModel;
use App\model\LoginHistoryModel;
use App\model\LogsModel;
use App\model\PrivilegesModel;
use App\model\TransactionLogsModel;
use App\model\TransactionModel;
use App\model\UserLogsModel;
use App\model\UserModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\JoinColumn;
use thiagoalessio\TesseractOCR\Option;

#[ORM\Entity]
#[ORM\Table(name: 'org_staff')]
#[ORM\UniqueConstraint(name: "name", columns: ["name"])]
class Staff{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $position;

    #[ORM\Column(type: 'string')]
    private string $img;

    #[ORM\ManyToOne(targetEntity: Staff::class)]
    private Staff|null $superior;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Staff
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Staff
    {
        $this->name = $name;
        return $this;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): Staff
    {
        $this->position = $position;
        return $this;
    }

    public function getImg(): string
    {
        return $this->img;
    }

    public function setImg(string $img): Staff
    {
        $this->img = $img;
        return $this;
    }

    public function getSuperior(): ?Staff
    {
        return $this->superior;
    }

    public function setSuperior(?Staff $superior): Staff
    {
        $this->superior = $superior;
        return $this;
    }

}