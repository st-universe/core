<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\KnCommentArchivRepository")
 * @Table(
 *     name="stu_kn_comments_archiv" )
 **/
class KnCommentArchiv implements KnCommentArchivInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="string", nullable=true)
     *
     */
    private ?string $version = '';

    /**
     * @Column(type="integer", nullable=true)
     * 
     */
    private ?int $former_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $post_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $user_id = 0;

    /**
     * @Column(type="string", nullable=true)
     *
     */
    private ?string $username = '';

    /**
     * @Column(type="string", nullable=true)
     *
     */
    private ?string $text = '';

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $date = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $deleted = null;

    /**
     *
     * @ManyToOne(targetEntity="KnPostArchiv")
     * @JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private KnPostArchivInterface $post;


    public function getId(): int
    {
        return $this->id;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getFormerId(): int
    {
        return $this->former_id;
    }

    public function getPostId(): int
    {
        return $this->post_id;
    }

    public function setPostId(int $postId): KnCommentArchivInterface
    {
        $this->post_id = $postId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): KnCommentArchivInterface
    {
        $this->username = $username;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): KnCommentArchivInterface
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): KnCommentArchivInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getPosting(): KnPostArchivInterface
    {
        return $this->post;
    }

    public function setPosting(KnPostArchivInterface $post): KnCommentArchivInterface
    {
        $this->post = $post;

        return $this;
    }

    public function setDeleted(int $timestamp): KnCommentArchivInterface
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
