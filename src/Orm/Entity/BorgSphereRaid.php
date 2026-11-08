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
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity]
#[Table(name: 'stu_borg_sphere_raid')]
#[UniqueConstraint(name: 'borg_sphere_raid_user_idx', columns: ['user_id'])]
class BorgSphereRaid
{
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    #[Column(type: 'integer')]
    private int $id;
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;
    #[Column(name: 'last_attempt_at', type: 'integer')]
    private int $lastAttemptAt = 0;
    #[Column(type: 'integer')]
    private int $attempts = 0;
    public function getId(): int
    {
        return $this->id;
    }
    public function getUser(): User
    {
        return $this->user;
    }
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
    public function getLastAttemptAt(): int
    {
        return $this->lastAttemptAt;
    }
    public function setLastAttemptAt(int $time): self
    {
        $this->lastAttemptAt = $time;
        return $this;
    }
    public function getAttempts(): int
    {
        return $this->attempts;
    }
    public function setAttempts(int $attempts): self
    {
        $this->attempts = $attempts;
        return $this;
    }
}
