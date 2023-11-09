<?php

namespace App\model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_logs')]
class UserLogsModel {

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\ManyToOne(targetEntity: UserModel::class, inversedBy: 'myLogs')]
    private ?UserModel $user = null;

    #[ORM\Column(type: 'string')]
    private $action;

    #[ORM\Column(type: 'datetime')]
    private $created_at;



    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return UserLogsModel
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getUser(): ?UserModel
    {
        return $this->user;
    }

    public function setUser(?UserModel $user): UserLogsModel
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     * @return UserLogsModel
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     * @return UserLogsModel
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }


}
