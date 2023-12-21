<?php

namespace App\model;

use App\lib\Time;
use App\model\enum\AnnouncementStatus;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'announcement_history')]
class AnnouncementHistoryModel
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\ManyToOne(targetEntity: AnnouncementModel::class, inversedBy: 'history')]
    private ?AnnouncementModel $announcement = null;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'string')]
    private $title;

    #[ORM\Column(type: 'string')]
    private $content;

    public function __construct()
    {
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

    public function getAnnouncement(): ?AnnouncementModel
    {
        return $this->announcement;
    }

    public function setAnnouncement(?AnnouncementModel $announcement): void
    {
        $this->announcement = $announcement;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }


}
