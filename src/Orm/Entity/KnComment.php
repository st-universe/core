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
use Stu\Orm\Repository\KnCommentRepository;

#[Table(name: 'stu_kn_comments')]
#[Index(name: 'kn_comment_post_idx', columns: ['post_id'])]
#[Index(name: 'kn_comment_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: KnCommentRepository::class)]
class KnComment
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $post_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'string')]
    private string $username = '';

    #[Column(type: 'string')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    #[ManyToOne(targetEntity: KnPost::class)]
    #[JoinColumn(name: 'post_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private KnPost $post;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getKnId(): int
    {
        return $this->post_id;
    }

    public function setPostId(int $postId): KnComment
    {
        $this->post_id = $postId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): KnComment
    {
        $this->user = $user;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): KnComment
    {
        $this->username = $username;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): KnComment
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): KnComment
    {
        $this->date = $date;

        return $this;
    }

    public function getPosting(): KnPost
    {
        return $this->post;
    }

    public function setPosting(KnPost $post): KnComment
    {
        $this->post = $post;

        return $this;
    }

    public function setDeleted(int $timestamp): KnComment
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
