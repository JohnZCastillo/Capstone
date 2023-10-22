<?php

namespace App\model;

use App\model\enum\UserRole;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\JoinColumn;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(name: "email", columns: ["email"])]
class UserModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'string')]
    private string $name; 

    #[ORM\Column(type: 'string')]
    private string $block; 

    #[ORM\Column(type: 'string')]
    private string $lot; 

    #[ORM\Column(type: 'string')]
    private string $email; 

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'boolean')]
    private string $isBlocked;

    #[ORM\OneToMany(targetEntity: TransactionModel::class, mappedBy: 'user')]
    private Collection|array $transactions;

    #[ORM\OneToMany(targetEntity: AnnouncementModel::class, mappedBy: 'user')]
    private Collection|array $posts;

    #[ORM\OneToMany(targetEntity: TransactionLogsModel::class, mappedBy: 'updatedBy')]
    private Collection|array $logs;

    #[ORM\OneToMany(targetEntity: LoginHistoryModel::class, mappedBy: 'user')]
    private Collection|array $loginHistory;

    #[ORM\OneToMany(targetEntity: IssuesModel::class, mappedBy: 'issues')]
    private Collection|array $issues;

    #[ORM\OneToMany(targetEntity: LogsModel::class, mappedBy: 'user')]
    private Collection|array $actionLogs;

    #[ORM\OneToMany(targetEntity: UserLogsModel::class, mappedBy: 'user')]
    private Collection|array $myLogs;

    #[ORM\OneToOne(targetEntity: PrivilegesModel::class, mappedBy: 'user',)]
    private  PrivilegesModel $privileges;

    #[ORM\Column(type: UserRole::class)]
    private $role;

    public function getIsBlocked(): string
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(string $isBlocked): UserModel
    {
        $this->isBlocked = $isBlocked;
        return $this;
    }

    public function getActionLogs(): Collection|array
    {
        return $this->actionLogs;
    }

    public function setActionLogs(Collection|array $actionLogs): UserModel
    {
        $this->actionLogs = $actionLogs;
        return $this;
    }

    public function getPrivileges(): PrivilegesModel
    {
        return $this->privileges;
    }

    public function setPrivileges(PrivilegesModel $privileges): UserModel
    {
        $this->privileges = $privileges;
        return $this;
    }


    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

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
     * Get the value of block
     */ 
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * Set the value of block
     *
     * @return  self
     */ 
    public function setBlock($block)
    {
        $this->block = $block;

        return $this;
    }

    /**
     * Get the value of lot
     */ 
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set the value of lot
     *
     * @return  self
     */ 
    public function setLot($lot)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of password
     */ 
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */ 
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of transactions
     */ 
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Set the value of transactions
     *
     * @return  self
     */ 
    public function setTransactions($transactions)
    {
        $this->transactions[] = $transactions;

        return $this;
    }

    /**
     * Get the value of posts
     */ 
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Set the value of posts
     *
     * @return  self
     */ 
    public function setPosts($posts)
    {
        $this->posts = $posts;

        return $this;
    }

    /**
     * Get the value of logs
     */ 
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Set the value of logs
     *
     * @return  self
     */ 
    public function setLogs($logs)
    {
        $this->logs = $logs;

        return $this;
    }

    /**
     * Get the value of role
     */ 
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the value of role
     *
     * @return  self
     */ 
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the value of issues
     */ 
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * Set the value of issues
     *
     * @return  self
     */ 
    public function setIssues($issues)
    {
        $this->issues = $issues;

        return $this;
    }

    /**
     * @return array|Collection
     */
    public function getLoginHistory(): Collection|array
    {
        return $this->loginHistory;
    }

    /**
     * @param array|Collection $loginHistory
     * @return UserModel
     */
    public function setLoginHistory(Collection|array $loginHistory): UserModel
    {
        $this->loginHistory = $loginHistory;
        return $this;
    }

}