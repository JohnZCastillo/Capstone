<?php

namespace App\model;

use App\model\enum\AnnouncementStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'announcement')]
class AnnouncementModel
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;
    #[ORM\Column(type: 'string')]
    private $title;

    #[ORM\Column(type: 'text')]
    private $content;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: AnnouncementStatus::class)]
    private $status;

    #[ORM\ManyToOne(targetEntity: UserModel::class, inversedBy: 'posts')]
    private ?UserModel $user = null;

    #[ORM\OneToMany(mappedBy: 'announcement', targetEntity: AnnouncementHistoryModel::class)]
    private Collection|array $history;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private \DateTime|null $pinDate;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => true])]
    private bool|null $pin;

    public function __construct()
    {
        $this->history = new ArrayCollection();
        $this->pin = false;
    }

    public function setPinDate(?\DateTime $pinDate): void
    {
        $this->pinDate = $pinDate;
    }

    public function setPin(?bool $pin): void
    {
        $this->pin = $pin;
    }

    public function getPinDate(): ?\DateTime
    {
        return $this->pinDate;
    }

    public function getPin(): ?bool
    {
        return $this->pin;
    }

    public function getHistory(): Collection|array
    {
        return $this->history;
    }

    public function setHistory(Collection|array $history): void
    {
        $this->history = $history;
    }


    /**
     * Get the value of title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @return  self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @return  self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }


    /**
     * Get the value of createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
