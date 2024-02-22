<?php

namespace App\model;

use App\lib\Encryptor;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'system_settings')]
class SystemSettings
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', nullable: true)]
    private $mailUsername;

    #[ORM\Column(type: 'string', nullable: true)]
    private $mailPassword;

    #[ORM\Column(type: 'string', nullable: true)]
    private $mailHost;

    #[ORM\Column(type: 'string', nullable: true, options: ["default"=>"/(\d{4}\s\d{3}\s\d{6})/i"])]
    private $regex;

    #[ORM\Column(type: 'text', nullable: true)]
    private $termsAndCondition;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $allowSignup;

    /**
     * @param $id
     */
    public function __construct()
    {
        $this->id = 1;
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
     * @return SystemSettings
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMailUsername()
    {
        return $this->mailUsername;
    }

    /**
     * @param mixed $mailUsername
     * @return SystemSettings
     */
    public function setMailUsername($mailUsername)
    {
        $this->mailUsername = $mailUsername;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMailPassword()
    {
        return Encryptor::decrypt($this->mailPassword);
    }

    /**
     * @param mixed $mailPassword
     * @return SystemSettings
     */
    public function setMailPassword($mailPassword)
    {

        $this->mailPassword = Encryptor::encrypt($mailPassword);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMailHost()
    {
        return $this->mailHost;
    }

    /**
     * @param mixed $mailHost
     * @return SystemSettings
     */
    public function setMailHost($mailHost)
    {
        $this->mailHost = $mailHost;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTermsAndCondition()
    {
        return $this->termsAndCondition;
    }

    /**
     * @param mixed $termsAndCondition
     * @return SystemSettings
     */
    public function setTermsAndCondition($termsAndCondition)
    {
        $this->termsAndCondition = $termsAndCondition;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllowSignup()
    {
        return $this->allowSignup;
    }

    /**
     * @param mixed $allowSignup
     * @return SystemSettings
     */
    public function setAllowSignup($allowSignup)
    {
        $this->allowSignup = $allowSignup;
        return $this;
    }

    public function getRegex()
    {
        return $this->regex;
    }

    public function setRegex($regex): void
    {
        $this->regex = $regex;
    }

}