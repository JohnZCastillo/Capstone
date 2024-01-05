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
#[ORM\Table(name: 'overview')]
class Overview{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private string|null $heroDescription;

    #[ORM\Column(type: 'string', nullable: true)]
    private string|null $heroImg;

    #[ORM\Column(type: 'text', nullable: true)]
    private string|null $aboutDescription;

    #[ORM\Column(type: 'string', nullable: true)]
    private string|null $aboutImg;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getHeroDescription(): ?string
    {
        return $this->heroDescription;
    }

    public function setHeroDescription(?string $heroDescription): void
    {
        $this->heroDescription = $heroDescription;
    }

    public function getHeroImg(): ?string
    {
        return $this->heroImg;
    }

    public function setHeroImg(?string $heroImg): void
    {
        $this->heroImg = $heroImg;
    }

    public function getAboutDescription(): ?string
    {
        return $this->aboutDescription;
    }

    public function setAboutDescription(?string $aboutDescription): void
    {
        $this->aboutDescription = $aboutDescription;
    }

    public function getAboutImg(): ?string
    {
        return $this->aboutImg;
    }

    public function setAboutImg(?string $aboutImg): void
    {
        $this->aboutImg = $aboutImg;
    }


}