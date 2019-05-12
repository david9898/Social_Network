<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Suggestion
 *
 * @ORM\Table(name="suggestions")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SuggestionRepository")
 */
class Suggestion
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_seen", type="boolean")
     */
    private $isSeen;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="sendSuggestions")
     * @ORM\JoinColumn(name="suggest_user", referencedColumnName="id")
     */
    private $suggestUser;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="acceptSuggestions")
     * @ORM\JoinColumn(name="accept_user", referencedColumnName="id")
     */
    private $acceptUser;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_disabled", type="boolean")
     */
    private $isDisabled;

    public function __construct()
    {
        $this->isSeen = false;
        $this->isDisabled = false;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isSeen
     *
     * @param boolean $isSeen
     *
     * @return Suggestion
     */
    public function setIsSeen($isSeen)
    {
        $this->isSeen = $isSeen;

        return $this;
    }

    /**
     * Get isSeen
     *
     * @return bool
     */
    public function getIsSeen()
    {
        return $this->isSeen;
    }

    /**
     * @return User
     */
    public function getSuggestUser()
    {
        return $this->suggestUser;
    }

    public function setSuggestUser($suggestUser)
    {
        $this->suggestUser = $suggestUser;
    }

    /**
     * @return User
     */
    public function getAcceptUser()
    {
        return $this->acceptUser;
    }


    public function setAcceptUser($acceptUser)
    {
        $this->acceptUser = $acceptUser;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * @param bool $isDisabled
     */
    public function setIsDisabled(bool $isDisabled)
    {
        $this->isDisabled = $isDisabled;
    }


}

