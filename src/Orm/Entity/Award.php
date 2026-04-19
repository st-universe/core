<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\AwardRepository;

#[Table(name: 'stu_award')]
#[Index(name: 'award_user_idx', columns: ['user_id'])]
#[Index(name: 'award_is_npc_idx', columns: ['is_npc'])]
#[Entity(repositoryClass: AwardRepository::class)]
class Award
{
    #[Id]
    #[Column(type: 'integer')]
    private int $id;

    #[Column(type: 'integer')]
    private int $prestige = 0;

    #[Column(type: 'text')]
    private string $description = '';

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $is_npc = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $user_id = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?User $user = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Award
    {
        $this->id = $id;

        return $this;
    }

    public function getPrestige(): int
    {
        return $this->prestige;
    }

    public function setPrestige(int $prestige): Award
    {
        $this->prestige = $prestige;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Award
    {
        $this->description = $description;

        return $this;
    }

    public function getIsNpc(): ?bool
    {
        return $this->is_npc;
    }

    public function setIsNpc(?bool $isNpc): Award
    {
        $this->is_npc = $isNpc;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $userId): Award
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): Award
    {
        $this->user = $user;

        return $this;
    }
}
