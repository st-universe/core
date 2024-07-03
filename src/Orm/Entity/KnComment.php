<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\KnCommentRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_kn_comments')]
#[Index(name: 'kn_comment_post_idx', columns: ['post_id'])]
#[Index(name: 'kn_comment_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: KnCommentRepository::class)]
class KnComment implements KnCommentInterface
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

    #[ManyToOne(targetEntity: 'KnPost')]
    #[JoinColumn(name: 'post_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private KnPostInterface $post;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getPostId(): int
    {
        return $this->post_id;
    }

    #[Override]
    public function setPostId(int $postId): KnCommentInterface
    {
        $this->post_id = $postId;

        return $this;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): KnCommentInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getUsername(): string
    {
        return $this->username;
    }

    #[Override]
    public function setUsername(string $username): KnCommentInterface
    {
        $this->username = $username;

        return $this;
    }

    #[Override]
    public function getText(): string
    {
        return $this->text;
    }

    #[Override]
    public function setText(string $text): KnCommentInterface
    {
        $this->text = $text;

        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): KnCommentInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getPosting(): KnPostInterface
    {
        return $this->post;
    }

    #[Override]
    public function setPosting(KnPostInterface $post): KnCommentInterface
    {
        $this->post = $post;

        return $this;
    }

    #[Override]
    public function setDeleted(int $timestamp): KnCommentInterface
    {
        $this->deleted = $timestamp;

        return $this;
    }

    #[Override]
    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
