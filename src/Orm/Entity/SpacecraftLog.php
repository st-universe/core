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
use Stu\Orm\Repository\SpacecraftLogRepository;

#[Table(name: 'stu_spacecraft_log')]
#[Index(name: 'spacecraft_log_spacecraft_idx', columns: ['spacecraft_id'])]
#[Index(name: 'spacecraft_log_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: SpacecraftLogRepository::class)]
class SpacecraftLog
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $spacecraft_id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $user_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $rump_id = null;

    #[Column(type: 'string', nullable: true)]
    private ?string $name = null;

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $date;

    #[Column(type: 'integer', nullable: true)]
    private ?int $edited = null;

    #[Column(type: 'boolean')]
    private bool $is_private = false;

    #[Column(type: 'integer', nullable: true)]
    private ?int $deleted = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: true, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setSpacecraft(Spacecraft $spacecraft): SpacecraftLog
    {
        $this->spacecraft_id = $spacecraft->getId();
        $this->setUser($spacecraft->getUser());
        $this->rump_id = $spacecraft->getRump()->getId();
        $this->name = $spacecraft->getName();

        return $this;
    }

    public function getSpacecraftId(): int
    {
        return $this->spacecraft_id;
    }

    public function setSpacecraftId(int $spacecraftId): SpacecraftLog
    {
        $this->spacecraft_id = $spacecraftId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): SpacecraftLog
    {
        $this->user = $user;
        $this->user_id = $user?->getId();

        return $this;
    }

    public function getRumpId(): ?int
    {
        return $this->rump_id;
    }

    public function setRumpId(?int $rumpId): SpacecraftLog
    {
        $this->rump_id = $rumpId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): SpacecraftLog
    {
        $this->name = $name;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): SpacecraftLog
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): SpacecraftLog
    {
        $this->date = $date;

        return $this;
    }

    public function getEdited(): ?int
    {
        return $this->edited;
    }

    public function setEdited(int $timestamp): SpacecraftLog
    {
        $this->edited = $timestamp;

        return $this;
    }

    public function setDeleted(int $timestamp): SpacecraftLog
    {
        $this->deleted = $timestamp;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }

    public function isPrivate(): bool
    {
        return $this->is_private;
    }

    public function setPrivate(bool $isPrivate): SpacecraftLog
    {
        $this->is_private = $isPrivate;

        return $this;
    }
}
