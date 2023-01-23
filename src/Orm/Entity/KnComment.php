<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\KnCommentRepository")
 * @Table(
 *     name="stu_kn_comments",
 *     indexes={
 *         @Index(name="kn_comment_post_idx", columns={"post_id"}),
 *         @Index(name="kn_comment_user_idx", columns={"user_id"})
 *     }
 * )
 **/
class KnComment implements KnCommentInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $post_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id = 0;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $username = '';

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $text = '';

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $date = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $deleted;

    /**
     * @var KnPostInterface
     *
     * @ManyToOne(targetEntity="KnPost")
     * @JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $post;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPostId(): int
    {
        return $this->post_id;
    }

    public function setPostId(int $postId): KnCommentInterface
    {
        $this->post_id = $postId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): KnCommentInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): KnCommentInterface
    {
        $this->username = $username;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): KnCommentInterface
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): KnCommentInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getPosting(): KnPostInterface
    {
        return $this->post;
    }

    public function setPosting(KnPostInterface $post): KnCommentInterface
    {
        $this->post = $post;

        return $this;
    }

    public function setDeleted(int $timestamp): KnCommentInterface
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
