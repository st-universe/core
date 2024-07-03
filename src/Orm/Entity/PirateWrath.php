<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\PirateWrathRepository;

#[Table(name: 'stu_pirate_wrath')]
#[Entity(repositoryClass: PirateWrathRepository::class)]
class PirateWrath implements PirateWrathInterface
{
    #[Id]
    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'integer')]
    private int $wrath = PirateWrathInterface::DEFAULT_WRATH;

    #[Column(type: 'integer', nullable: true)]
    private ?int $protection_timeout = null;

    #[OneToOne(targetEntity: 'User', inversedBy: 'pirateWrath')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): PirateWrathInterface
    {
        $this->user = $user;
        $this->user_id = $user->getId();

        return $this;
    }

    #[Override]
    public function getWrath(): int
    {
        return $this->wrath;
    }

    #[Override]
    public function setWrath(int $wrath): PirateWrathInterface
    {
        $this->wrath = $wrath;

        return $this;
    }

    #[Override]
    public function getProtectionTimeout(): ?int
    {
        return $this->protection_timeout;
    }

    #[Override]
    public function setProtectionTimeout(?int $timestamp): PirateWrathInterface
    {
        $this->protection_timeout = $timestamp;

        return $this;
    }
}
