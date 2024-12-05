<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;

#[Table(name: 'stu_user_referer')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\UserRefererRepository')]
class UserReferer implements UserRefererInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[OneToOne(targetEntity: 'User', inversedBy: 'referer')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Column(type: 'text')]
    private string $referer;

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
    public function setUser(UserInterface $user): UserRefererInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getReferer(): string
    {
        return $this->referer;
    }

    #[Override]
    public function setReferer(string $referer): UserRefererInterface
    {
        $this->referer = $referer;
        return $this;
    }
}
