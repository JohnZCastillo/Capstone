<?php

namespace App\model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'issues_messages')]
class IssuesMessages{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'text')]
    private $message;

    #[ORM\Column(type: 'boolean', options: ['default'=> false])]
    private bool $image;

    #[ORM\Column(type: 'boolean', options: ['default'=> false])]
    private bool $file;

    #[ORM\ManyToOne(targetEntity: UserModel::class)]
    private ?UserModel $user = null;

    #[ORM\ManyToOne(targetEntity: IssuesModel::class, inversedBy: 'messages')]
    private ?IssuesModel $issue = null;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->image = false;
        $this->file = false;
    }

    public function isFile(): bool
    {
        return $this->file;
    }

    public function setFile(bool $file): void
    {
        $this->file = $file;
    }

    public function isImage(): bool
    {
        return $this->image;
    }

    public function setImage(bool $image): void
    {
        $this->image = $image;
    }
    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message): void
    {
        $this->message = $message;
    }

    public function getUser(): ?UserModel
    {
        return $this->user;
    }

    public function setUser(?UserModel $user): void
    {
        $this->user = $user;
    }

    public function getIssue(): ?IssuesModel
    {
        return $this->issue;
    }

    public function setIssue(?IssuesModel $issue): void
    {
        $this->issue = $issue;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "message" => $this->message,
            "isImage" => $this->isImage(),
            "userId" => $this->user->getId(),
            "issueId" => $this->issue->getId(),
        ];
    }
}