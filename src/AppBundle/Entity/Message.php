<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 *
 * @ORM\Table(name="messages")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MessageRepository")
 */
class Message
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
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=255)
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime")
     */
    private $dateAdded;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_delivered", type="boolean")
     */
    private $isDelivered;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_seen", type="boolean")
     */
    private $isSeen;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="acceptMessages")
     * @ORM\JoinColumn(name="accept_user", referencedColumnName="id")
     */
    private $acceptUser;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="sendMessages")
     * @ORM\JoinColumn(name="send_user", referencedColumnName="id")
     */
    private $sendUser;

    public function __construct()
    {
        $this->isDelivered = false;
        $this->isSeen = false;
        $this->dateAdded = new \DateTime();
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
     * Set content
     *
     * @param string $content
     *
     * @return Message
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     *
     * @return Message
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Set isDelivered
     *
     * @param boolean $isDelivered
     *
     * @return Message
     */
    public function setIsDelivered($isDelivered)
    {
        $this->isDelivered = $isDelivered;

        return $this;
    }

    /**
     * Get isDelivered
     *
     * @return bool
     */
    public function getIsDelivered()
    {
        return $this->isDelivered;
    }

    /**
     * Set isSeen
     *
     * @param boolean $isSeen
     *
     * @return Message
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
    public function getAcceptUser()
    {
        return $this->acceptUser;
    }

    /**
     * @param User $acceptUser
     */
    public function setAcceptUser($acceptUser)
    {
        $this->acceptUser = $acceptUser;
    }

    /**
     * @return User
     */
    public function getSendUser()
    {
        return $this->sendUser;
    }

    /**
     * @param User $sendUser
     */
    public function setSendUser($sendUser)
    {
        $this->sendUser = $sendUser;
    }

}

