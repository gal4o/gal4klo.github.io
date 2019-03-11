<?php

namespace BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="BlogBundle\Repository\UserRepository")
 */
class User implements UserInterface
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
     * @Assert\Email(
     *     message = "This email is not a valid.",
     *     checkMX = true
     * )
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

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
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @Assert\NotNull()
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "This value is too short.",
     *      maxMessage = "This value is too long."
     * )
     * @Assert\Regex(
     *     pattern="/[A-Za-z0-9 ]+/",
     *     match=false,
     *     message="Your name cannot contain a number"
     * )
     * @var string
     *
     * @ORM\Column(name="fullName", type="string", length=255)
     */
    private $fullName;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="BlogBundle\Entity\Article", mappedBy="author")
     */
    private $articles;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="BlogBundle\Entity\Role", inversedBy="users")
     * @ORM\JoinTable(name="users_roles",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *     )
     */
    private $roles;

    /**
     * @var ArrayCollection|Comment[]
     * @ORM\OneToMany(targetEntity="BlogBundle\Entity\Comment", mappedBy="author")
     */
    private $comments;

    /**
     * @var ArrayCollection|Message[]
     * @ORM\OneToMany(targetEntity="BlogBundle\Entity\Message", mappedBy="sender")
     */
    private $senders;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="BlogBundle\Entity\Article", inversedBy="likers")
     * @ORM\JoinTable(name="users_likes",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id")}
     *     )
     */
    private $likes;
    /**
     * @var ArrayCollection|Message[]
     * @ORM\OneToMany(targetEntity="BlogBundle\Entity\Message", mappedBy="recipient")
     */
    private $recipients;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->roles=new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->senders = new ArrayCollection();
        $this->recipients = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    /**
     *
     * @param Article $article
     * @return bool
     */
    public function isLiker(Article $article)
    {
        $currentUserId = $this->getId();
        foreach ($article->getLikers() as $user) {
            if ($user->getId()===$currentUserId) {
                return true;
            }
            return false;
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param Article $likes
     * @return User
     */
    public function addLikes(Article $likes)
    {
        $this->likes[] = $likes;
        return $this;
    }

    /**
     * @param Article $likes
     * @return User
     */
    public function removeLikes(Article $likes)
    {
        $this->getLikes()->removeElement($likes);
        return $this;
    }

    /**
     * @return Message[]|ArrayCollection
     */
    public function getSenderMessages()
    {
        return $this->senders;
    }

    /**
     * @return Message[]|ArrayCollection
     */
    public function getRecipientMessages()
    {
        return $this->recipients;
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
     * @return User
     */
    public function addComment(Comment $comment = null)
    {
        $this->comments[] = $comment;
        return $this;
    }


    /**
     * @return bool
     */
    public function isAdmin()
    {
        return in_array("ROLE_ADMIN", $this->getRoles());
    }

    /**
     * @param Article $article|Comment $article
     * @return bool
     */
    public function isAuthor($article)
    {
        return $article->getAuthor()->getId() == $this->getId();
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param \BlogBundle\Entity\Article $article
     * @return User
     */
    public function addArticle(Article $article)
    {
        $this->articles[] = $article;
        return $this;
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
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     *
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return array('ROLE_USER');
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return array (Role|string)[] The user roles
     */
    public function getRoles()
    {
        $stringRoles = [];
        foreach ($this->roles as $role)
        {
            /** @var $role Role */
            $stringRoles[] = $role->getRole();
        }
        return $stringRoles;
    }

    /**
     * @param Role $role
     * @return User
     */
    public function setRoles($role)
    {
        $this->roles[] = $role;
        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}

