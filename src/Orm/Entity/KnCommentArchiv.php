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
use Doctrine\ORM\Mapping\UniqueConstraint;
use Override;
use Stu\Orm\Repository\KnCommentArchivRepository;

#[Table(name: 'stu_kn_comments_archiv')]
#[UniqueConstraint(name: 'unique_comments_former_id', columns: ['former_id'])]
#[Entity(repositoryClass: KnCommentArchivRepository::class)]
class KnCommentArchiv implements KnCommentArchivInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $version = '';

    #[Column(type: 'integer')]
    private int $former_id = 0;

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

    #[ManyToOne(targetEntity: KnPostArchiv::class)]
    #[JoinColumn(name: 'post_id', nullable: false, referencedColumnName: 'former_id', onDelete: 'CASCADE')]
    private KnPostArchivInterface $post;


    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getVersion(): ?string
    {
        return $this->version;
    }

    #[Override]
    public function getFormerId(): int
    {
        return $this->former_id;
    }

    #[Override]
    public function getKnId(): int
    {
        return $this->post_id;
    }

    #[Override]
    public function setPostId(int $postId): KnCommentArchivInterface
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
    public function getUsername(): string
    {
        return $this->username;
    }

    #[Override]
    public function setUsername(string $username): KnCommentArchivInterface
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
    public function setText(string $text): KnCommentArchivInterface
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
    public function setDate(int $date): KnCommentArchivInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getPosting(): KnPostArchivInterface
    {
        return $this->post;
    }

    #[Override]
    public function setPosting(KnPostArchivInterface $post): KnCommentArchivInterface
    {
        $this->post = $post;

        return $this;
    }

    #[Override]
    public function setDeleted(int $timestamp): KnCommentArchivInterface
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
