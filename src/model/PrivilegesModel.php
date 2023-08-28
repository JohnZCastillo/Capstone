<?php

namespace App\model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;

#[ORM\Entity]
#[ORM\Table(name: 'privileges')]
class PrivilegesModel{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'boolean')]
    private $userPayment;

    #[ORM\Column(type: 'boolean')]
    private $userAnnouncement;

    #[ORM\Column(type: 'boolean')]
    private $userIssues;

    #[ORM\Column(type: 'boolean')]
    private $adminPayment;

    #[ORM\Column(type: 'boolean')]
    private $adminAnnouncement;

    #[ORM\Column(type: 'boolean')]
    private $adminIssues;

    #[ORM\Column(type: 'boolean')]
    private $adminUser;


    #[ORM\OneToOne(targetEntity: UserModel::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private UserModel $user;

    public function getUser(): UserModel
    {
        return $this->user;
    }

    public function setUser(UserModel $user): PrivilegesModel
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminUser()
    {
        return $this->adminUser;
    }

    /**
     * @param mixed $adminUser
     * @return PrivilegesModel
     */
    public function setAdminUser($adminUser)
    {
        $this->adminUser = $adminUser;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return PrivilegesModel
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserPayment()
    {
        return $this->userPayment;
    }

    /**
     * @param mixed $userPayment
     * @return PrivilegesModel
     */
    public function setUserPayment($userPayment)
    {
        $this->userPayment = $userPayment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserAnnouncement()
    {
        return $this->userAnnouncement;
    }

    /**
     * @param mixed $userAnnouncement
     * @return PrivilegesModel
     */
    public function setUserAnnouncement($userAnnouncement)
    {
        $this->userAnnouncement = $userAnnouncement;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserIssues()
    {
        return $this->userIssues;
    }

    /**
     * @param mixed $userIssues
     * @return PrivilegesModel
     */
    public function setUserIssues($userIssues)
    {
        $this->userIssues = $userIssues;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminPayment()
    {
        return $this->adminPayment;
    }

    /**
     * @param mixed $adminPayment
     * @return PrivilegesModel
     */
    public function setAdminPayment($adminPayment)
    {
        $this->adminPayment = $adminPayment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminAnnouncement()
    {
        return $this->adminAnnouncement;
    }

    /**
     * @param mixed $adminAnnouncement
     * @return PrivilegesModel
     */
    public function setAdminAnnouncement($adminAnnouncement)
    {
        $this->adminAnnouncement = $adminAnnouncement;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminIssues()
    {
        return $this->adminIssues;
    }

    /**
     * @param mixed $adminIssues
     * @return PrivilegesModel
     */
    public function setAdminIssues($adminIssues)
    {
        $this->adminIssues = $adminIssues;
        return $this;
    }

}