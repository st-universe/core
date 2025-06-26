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
use Stu\Orm\Repository\UserCharacterRepository;

#[Entity(repositoryClass: UserCharacterRepository::class)]
#[Table(name: 'stu_user_character')]
class UserCharacter
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[Column(type: 'string')]
    private string $name;

    #[Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Column(type: 'string', length: 32, nullable: true)]
    private ?string $avatar = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $former_user_id = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): UserCharacter
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): UserCharacter
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): UserCharacter
    {
        $this->description = $description;
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): UserCharacter
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getFormerUserId(): ?int
    {
        return $this->former_user_id;
    }

    public function setFormerUserId(?int $formerUserId): UserCharacter
    {
        $this->former_user_id = $formerUserId;
        return $this;
    }
}
