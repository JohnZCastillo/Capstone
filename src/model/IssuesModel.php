<?php

namespace App\model;

use App\lib\Time;
use App\model\enum\IssuesStatus;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

//Please add updated at
//and id who take the action
#[ORM\Entity]
#[ORM\Table(name: 'issues')]
class IssuesModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;
    
    #[ORM\Column(type: 'text')]
    private $content;

    #[ORM\Column(type: 'string')]
    private $title;
    
    #[ORM\Column(type: 'string')]
    private $type;

   #[ORM\Column(type: IssuesStatus::class)]
    private $status;
    
    #[ORM\Column(type: 'text')]
    private $action;

    #[ORM\Column(type: 'string')]
    private $target;

    #[ORM\ManyToOne(targetEntity: UserModel::class, inversedBy: 'issues',)]
    private ?UserModel $user = null;

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param mixed $target
     * @return IssuesModel
     */
    public function setTarget($target)
    {
        $this->target = $target;
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
     * Get the value of createdAt
     */ 
    public function getCreatedAt()
    {
        return Time::convertToString($this->createdAt);
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

    /**
     * Get the value of action
     */ 
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the value of action
     *
     * @return  self
     */ 
    public function setAction($action)
    {
        $this->action = $action;

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
     * Get the value of type
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */ 
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}