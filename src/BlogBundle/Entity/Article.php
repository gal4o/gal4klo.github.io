<?php

namespace BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Article
 *
 * @ORM\Table(name="articles")
 * @ORM\Entity(repositoryClass="BlogBundle\Repository\ArticleRepository")
 */
class Article
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
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "This value is too short.",
     *      maxMessage = "This value is too long."
     * )
     *
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @Assert\NotNull()
     * @Assert\Length(
     *      min = 3,
     *      minMessage = "This value is too short.",
     * )
     * @var string
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
     * @var string
     */
    private $summary;

    /**
     * @var int
     * @ORM\Column(name="authorId", type="integer")
     */
    private $authorId;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="BlogBundle\Entity\User", inversedBy="articles")
     * @ORM\JoinColumn(name="authorId", referencedColumnName="id")
     */
    private $author;

    /**
     * @var int
     *
     * @ORM\Column(name="viewsCount", type="integer")
     */
    private $viewsCount;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="BlogBundle\Entity\User", mappedBy="likes")
     */
    private $likers;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", nullable=true)
     */
    private $image;

    /**
     * @var ArrayCollection|Comment[]
     *
     * @ORM\OneToMany(targetEntity="BlogBundle\Entity\Comment", mappedBy="article", cascade={"remove"})
     */
    private $comments;

    public function __construct()
    {
        $this->dateAdded = new \DateTime('now');
        $this->comments = new ArrayCollection();
        $this->likers = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|User[]
     */
    public function getLikers()
    {
        return $this->likers;
    }

    /**
     * @param User $user
     * @return Article
     */
    public function setLikers(User $user)
    {
        $this->likers[] = $user;
        return $this;
    }

    /**
     * @param User $likes
     * @return Article
     */
    public function removeLikers(User $likes)
    {
        $this->getLikers()->removeElement($likes);
        return $this;
    }

    /**
     * @return Comment[]|ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Comment|null $comment
     * @return Article
     */
    public function addComment(Comment $comment = null)
    {
        $this->comments[] = $comment;
        return $this;
    }

    /**
     * @param \BlogBundle\Entity\User $author
     * @return Article
     */
    public function setAuthor(User $author = null)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return \BlogBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param int $authorId
     * @return Article
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;
        return $this;
    }

    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * @param string
     */
    private function setSummary()
    {
        $this->summary = substr($this->getContent(), 0, strlen($this->getContent())/2)."...";
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        if ($this->summary === null)
        {
            $this->setSummary();
        }
        return $this->summary;
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
     * Set title
     *
     * @param string $title
     *
     * @return Article
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Article
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
     * @return Article
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
     * @return integer
     */
    public function getViewsCount()
    {
        return $this->viewsCount;
    }

    /**
     * @param integer $viewsCount
     */
    public function setViewsCount($viewsCount)
    {
        $this->viewsCount = $viewsCount;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }
}

