<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Lib\Pirate\Component\PirateWrathManager;
use Stu\Orm\Repository\PirateWrathRepository;

#[Table(name: 'stu_pirate_wrath')]
#[Entity(repositoryClass: PirateWrathRepository::class)]
class PirateWrath
{
    #[Column(type: 'integer')]
    private int $wrath = PirateWrathManager::DEFAULT_WRATH;

    #[Column(type: 'integer', nullable: true)]
    private ?int $protection_timeout = null;

    #[Id]
    #[OneToOne(targetEntity: User::class, inversedBy: 'pirateWrath')]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): PirateWrath
    {
        $this->user = $user;

        return $this;
    }

    public function getWrath(): int
    {
        return $this->wrath;
    }

    public function setWrath(int $wrath): PirateWrath
    {
        $this->wrath = $wrath;

        return $this;
    }

    public function getProtectionTimeout(): ?int
    {
        return $this->protection_timeout;
    }

    public function setProtectionTimeout(?int $timestamp): PirateWrath
    {
        $this->protection_timeout = $timestamp;

        return $this;
    }
}
