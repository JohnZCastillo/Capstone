<?php

namespace App\model;

use Doctrine\ORM\Mapping as ORM;

//loginTime and Logout Time should timestamp

#[ORM\Entity]
#[ORM\Table(name: 'login_history')]
#[ORM\UniqueConstraint(name: "session", columns: ["session"])]
class LoginHistoryModel {

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;
    #[ORM\Column(type: 'string')]
    private $device;
    #[ORM\Column(type: 'string')]
    private $ip;
    #[ORM\Column(type: 'string')]
    private $session;

    #[ORM\Column(type: 'date')]
    private $loginDate;

    #[ORM\Column(type: 'date',nullable: true)]
    private $logoutDate;

    #[ORM\ManyToOne(targetEntity: UserModel::class, inversedBy: 'loginHistory')]
    private ?UserModel $user = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return LoginHistoryModel
     */
    public function setId(?int $id): LoginHistoryModel
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param mixed $device
     * @return LoginHistoryModel
     */
    public function setDevice($device)
    {
        $this->device = $device;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     * @return LoginHistoryModel
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param mixed $session
     * @return LoginHistoryModel
     */
    public function setSession($session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLoginDate()
    {
        return $this->loginDate;
    }

    /**
     * @param mixed $loginDate
     * @return LoginHistoryModel
     */
    public function setLoginDate($loginDate)
    {
        $this->loginDate = $loginDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogoutDate()
    {
        return $this->logoutDate;
    }

    /**
     * @param mixed $logoutDate
     * @return LoginHistoryModel
     */
    public function setLogoutDate($logoutDate)
    {
        $this->logoutDate = $logoutDate;
        return $this;
    }

    /**
     * @return UserModel|null
     */
    public function getUser(): ?UserModel
    {
        return $this->user;
    }

    /**
     * @param UserModel|null $user
     * @return LoginHistoryModel
     */
    public function setUser(?UserModel $user): LoginHistoryModel
    {
        $this->user = $user;
        return $this;
    }

}
