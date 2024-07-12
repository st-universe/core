<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\UserLockRepository;

#[Table(name: 'stu_user_lock')]
#[Index(name: 'user_lock_user_idx', columns: ['user_id'])]
#[Entity(repositoryClass: UserLockRepository::class)]
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

    #[OneToOne(targetEntity: 'User', inversedBy: 'userLock')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?UserInterface $user = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setUserId(?int $userId): UserLockInterface
    {
        $this->user_id = $userId;
        return $this;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(?UserInterface $user): UserLockInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function setFormerUserId(?int $userId): UserLockInterface
    {
        $this->former_user_id = $userId;
        return $this;
    }

    #[Override]
    public function getRemainingTicks(): int
    {
        return $this->remaining_ticks;
    }

    #[Override]
    public function setRemainingTicks(int $count): UserLockInterface
    {
        $this->remaining_ticks = $count;
        return $this;
    }

    #[Override]
    public function getReason(): string
    {
        return $this->reason;
    }

    #[Override]
    public function setReason(string $reason): UserLockInterface
    {
        $this->reason = $reason;

        return $this;
    }
}
