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

#[Table(name: 'stu_user_lock')]
#[Index(name: 'user_lock_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\UserLockRepository')]
class UserLock implements UserLockInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $user_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $former_user_id = null;

    #[Column(type: 'integer')]
    private int $remaining_ticks = 0;

    #[Column(type: 'text')]
    private string $reason = '';

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?UserInterface $user = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setUserId(?int $userId): UserLockInterface
    {
        $this->user_id = $userId;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): UserLockInterface
    {
        $this->user = $user;
        return $this;
    }

    public function setFormerUserId(?int $userId): UserLockInterface
    {
        $this->former_user_id = $userId;
        return $this;
    }

    public function getRemainingTicks(): int
    {
        return $this->remaining_ticks;
    }

    public function setRemainingTicks(int $count): UserLockInterface
    {
        $this->remaining_ticks = $count;
        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): UserLockInterface
    {
        $this->reason = $reason;

        return $this;
    }
}
