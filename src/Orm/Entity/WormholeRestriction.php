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
use Stu\Orm\Repository\WormholeRestrictionRepository;

#[Table(name: 'stu_wormhole_restrictions')]
#[Entity(repositoryClass: WormholeRestrictionRepository::class)]
class WormholeRestriction
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: WormholeEntry::class)]
    #[JoinColumn(name: 'wormhole_entry_id', nullable: false, referencedColumnName: 'id')]
    private WormholeEntry $wormholeEntry;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[Column(type: 'integer', nullable: true)]
    private ?int $mode = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getWormholeEntry(): WormholeEntry
    {
        return $this->wormholeEntry;
    }

    public function setWormholeEntry(WormholeEntry $wormholeEntry): WormholeRestriction
    {
        $this->wormholeEntry = $wormholeEntry;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): WormholeRestriction
    {
        $this->user = $user;
        return $this;
    }

    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(?int $mode): WormholeRestriction
    {
        $this->mode = $mode;
        return $this;
    }
}
