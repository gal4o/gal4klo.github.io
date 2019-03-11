<?php

namespace BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Message
 *
 * @ORM\Table(name="messages")
 * @ORM\Entity(repositoryClass="BlogBundle\Repository\MessageRepository")
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
     * @Assert\NotNull()
     * @var string
     *
     * @ORM\Column(name="about", type="string", length=255)
     */
    private $about;

    /**
     * @Assert\NotNull()
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateAdded", type="datetime")
     */
    private $dateAdded;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="BlogBundle\Entity\User", inversedBy="senders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="BlogBundle\Entity\User", inversedBy="recipients")
     * @ORM\JoinColumn(nullable=false)
     */
    private $recipient;

    /**
     * @var bool
     * @ORM\Column(name="is_readed", nullable=false, type="boolean")
     */
    private $isReader;

    public function __construct()
    {
        $this->dateAdded = new \DateTime('now');
    }

    /**
     * @return bool
     */
    public function isReader()
    {
        return $this->isReader;
    }

    /**
     * @param bool $isReader
     * @return Message
     */
    public function setIsReader($isReader)
    {
        $this->isReader = $isReader;
        return $this;
    }


    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     * @return Message
     */
    public function setSender(User $sender)
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return User
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param User $recipient
     * @return Message
     */
    public function setRecipient(User $recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }



    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set about.
     *
     * @param string $about
     *
     * @return Message
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about.
     *
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set content.
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
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set dateAdded.
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
     * Get dateAdded.
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }
}
