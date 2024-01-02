<?php

namespace App\model;

use App\lib\Encryptor;
use App\model\enum\UserRole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

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

    #[ORM\Column(type: 'boolean', options: ["default"=> false])]
    private bool $isBlocked;

    #[ORM\Column(type: 'date', nullable: true)]
    private \DateTime|null $blockDate;

    #[ORM\Column(type: 'boolean', options: ["default"=> true])]
    private bool $sharedPayments;

    #[ORM\Column(type: 'boolean', options: ["default"=> false])]
    private bool $verified;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: TransactionModel::class)]
    private Collection|array $transactions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: AnnouncementModel::class)]
    private Collection|array $posts;

    #[ORM\OneToMany(mappedBy: 'updatedBy', targetEntity: TransactionLogsModel::class)]
    private Collection|array $logs;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: LoginHistoryModel::class)]
    private Collection|array $loginHistory;

    #[ORM\OneToMany(mappedBy: 'issues', targetEntity: IssuesModel::class)]
    private Collection|array $issues;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: LogsModel::class)]
    private Collection|array $actionLogs;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserLogsModel::class)]
    private Collection|array $myLogs;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: PrivilegesModel::class,)]
    private  PrivilegesModel $privileges;

    #[ORM\Column(type: UserRole::class)]
    private $role;

    /**
     * @param int|null $id
     */
    public function __construct()
    {
        $this->myLogs = new ArrayCollection();
        $this->actionLogs = new ArrayCollection();
        $this->issues = new ArrayCollection();
        $this->loginHistory = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->sharedPayments = true;
        $this->verified = false;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): void
    {
        $this->verified = $verified;
    }


    public function getBlockDate(): \DateTime|null
    {
        return $this->blockDate;
    }

    public function setBlockDate(\DateTime $blockDate): void
    {
        $this->blockDate = $blockDate;
    }

    public function getMyLogs(): Collection|array
    {
        return $this->myLogs;
    }

    public function setMyLogs(Collection|array $myLogs): UserModel
    {
        $this->myLogs = $myLogs;
        return $this;
    }

    public function getIsBlocked()
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
        return Encryptor::decrypt($this->password);
    }

    /**
     * Set the value of password
     *
     * @return  self
     */ 
    public function setPassword($password)
    {
        $this->password = Encryptor::encrypt($password);

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