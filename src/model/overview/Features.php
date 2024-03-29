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
#[ORM\Table(name: 'features')]
class Features{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'string')]
    private string $img;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getImg(): string
    {
        return $this->img;
    }

    public function setImg(string $img)
    {
        $this->img = $img;
    }
}