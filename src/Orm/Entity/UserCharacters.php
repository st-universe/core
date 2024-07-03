<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: 'Stu\Orm\Repository\UserCharactersRepository')]
#[Table(name: 'stu_user_characters')]
class UserCharacters implements UserCharactersInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Column(type: 'string')]
    private string $name;

    #[Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Column(type: 'string', length: 32, nullable: true)]
    private ?string $avatar = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $former_user_id = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): UserCharactersInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): UserCharactersInterface
    {
        $this->name = $name;
        return $this;
    }

    #[Override]
    public function getDescription(): ?string
    {
        return $this->description;
    }

    #[Override]
    public function setDescription(?string $description): UserCharactersInterface
    {
        $this->description = $description;
        return $this;
    }

    #[Override]
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    #[Override]
    public function setAvatar(?string $avatar): UserCharactersInterface
    {
        $this->avatar = $avatar;
        return $this;
    }

    #[Override]
    public function getFormerUserId(): ?int
    {
        return $this->former_user_id;
    }

    #[Override]
    public function setFormerUserId(?int $formerUserId): UserCharactersInterface
    {
        $this->former_user_id = $formerUserId;
        return $this;
    }
}
