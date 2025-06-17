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
use Override;
use Stu\Orm\Repository\WormholeRestrictionRepository;

#[Table(name: 'stu_wormhole_restrictions')]
#[Entity(repositoryClass: WormholeRestrictionRepository::class)]
class WormholeRestriction implements WormholeRestrictionInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: 'WormholeEntry')]
    #[JoinColumn(name: 'wormhole_entry_id', referencedColumnName: 'id')]
    private WormholeEntryInterface $wormholeEntry;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private UserInterface $user;

    #[Column(type: 'integer', nullable: true)]
    private ?int $mode = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getWormholeEntry(): WormholeEntryInterface
    {
        return $this->wormholeEntry;
    }

    #[Override]
    public function setWormholeEntry(WormholeEntryInterface $wormholeEntry): WormholeRestrictionInterface
    {
        $this->wormholeEntry = $wormholeEntry;
        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): WormholeRestrictionInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getMode(): ?int
    {
        return $this->mode;
    }

    #[Override]
    public function setMode(?int $mode): WormholeRestrictionInterface
    {
        $this->mode = $mode;
        return $this;
    }
}
