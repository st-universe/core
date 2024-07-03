<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\UserTagRepository;
use Override;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_user_tag')]
#[Entity(repositoryClass: UserTagRepository::class)]
class UserTag implements UserTagInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'integer')]
    private int $tag_type_id;

    #[Column(type: 'datetime')]
    private ?DateTimeInterface $date = null;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setTagTypeId(int $tagTypeId): UserTagInterface
    {
        $this->tag_type_id = $tagTypeId;
        return $this;
    }

    #[Override]
    public function getTagTypeId(): int
    {
        return $this->tag_type_id;
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
    public function setUser(UserInterface $user): UserTagInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    #[Override]
    public function setDate(DateTimeInterface $date): UserTagInterface
    {
        $this->date = $date;

        return $this;
    }
}
