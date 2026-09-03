<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\UserLastActionRepository;

#[Table(name: 'stu_user_last_action')]
#[Entity(repositoryClass: UserLastActionRepository::class)]
class UserLastAction
{
    #[Id]
    #[OneToOne(targetEntity: User::class, inversedBy: 'lastAction')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[Column(type: 'integer')]
    private int $timestamp = 0;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp): UserLastAction
    {
        $this->timestamp = $timestamp;
        return $this;
    }
}
