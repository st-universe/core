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
use Stu\Component\Map\WormholeEntryTypeEnum;
use Stu\Orm\Repository\WormholeEntryRepository;

#[Table(name: 'stu_wormhole_entry')]
#[Entity(repositoryClass: WormholeEntryRepository::class)]
class WormholeEntry implements WormholeEntryInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $map_id;

    #[Column(type: 'integer')]
    private int $system_id;

    #[Column(type: 'integer')]
    private int $system_map_id;

    #[Column(type: 'string', length: 10, enumType: WormholeEntryTypeEnum::class)]
    private WormholeEntryTypeEnum $type = WormholeEntryTypeEnum::BOTH;

    #[Column(type: 'integer', nullable: true)]
    private ?int $last_used = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $cooldown = null;

    #[ManyToOne(targetEntity: 'Map', inversedBy: 'wormholeEntries')]
    #[JoinColumn(name: 'map_id', referencedColumnName: 'id')]
    private MapInterface $map;

    #[ManyToOne(targetEntity: 'StarSystem')]
    #[JoinColumn(name: 'system_id', referencedColumnName: 'id')]
    private StarSystemInterface $starSystem;

    #[ManyToOne(targetEntity: 'StarSystemMap', inversedBy: 'wormholeEntries')]
    #[JoinColumn(name: 'system_map_id', referencedColumnName: 'id')]
    private StarSystemMapInterface $systemMap;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getMap(): MapInterface
    {
        return $this->map;
    }

    #[Override]
    public function getSystem(): StarSystemInterface
    {
        return $this->starSystem;
    }

    #[Override]
    public function getSystemMap(): StarSystemMapInterface
    {
        return $this->systemMap;
    }

    #[Override]
    public function setLastUsed(int $lastUsed): WormholeEntryInterface
    {
        $this->last_used = $lastUsed;

        return $this;
    }

    #[Override]
    public function isUsable(LocationInterface $location): bool
    {
        if (
            $this->last_used !== null && $this->cooldown !== null
            && $this->last_used + $this->cooldown * 60 > time()
        ) {
            return false;
        }

        if ($this->type === WormholeEntryTypeEnum::BOTH) {
            return true;
        }

        if ($this->type === WormholeEntryTypeEnum::MAP_TO_W) {
            return $location === $this->map;
        }

        return $location === $this->systemMap;
    }
}
