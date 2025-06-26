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
use Stu\Orm\Repository\KnCommentArchivRepository;

#[Table(name: 'stu_kn_comments_archiv')]
#[UniqueConstraint(name: 'unique_comments_former_id', columns: ['former_id'])]
#[Entity(repositoryClass: KnCommentArchivRepository::class)]
class KnCommentArchiv
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
    private KnPostArchiv $post;


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

    public function getKnId(): int
    {
        return $this->post_id;
    }

    public function setPostId(int $postId): KnCommentArchiv
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

    public function setUsername(string $username): KnCommentArchiv
    {
        $this->username = $username;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): KnCommentArchiv
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): KnCommentArchiv
    {
        $this->date = $date;

        return $this;
    }

    public function getPosting(): KnPostArchiv
    {
        return $this->post;
    }

    public function setPosting(KnPostArchiv $post): KnCommentArchiv
    {
        $this->post = $post;

        return $this;
    }

    public function setDeleted(int $timestamp): KnCommentArchiv
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }
}
