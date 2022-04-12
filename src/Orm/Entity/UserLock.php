<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\UserLockRepository")
 * @Table(
 *     name="stu_user_lock"
 * )
 **/
class UserLock implements UserLockInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer", nullable=true) */
    private $user_id;

    /** @Column(type="integer", nullable=true) */
    private $former_user_id;

    /** @Column(type="integer") */
    private $remaining_ticks = 0;

    /** @Column(type="text") */
    private $reason = '';

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

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

    public function getUser(): UserInterface
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
