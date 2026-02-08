<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Map\WormholeEntryTypeEnum;
use Stu\Orm\Repository\WormholeEntryRepository;

#[Table(name: 'stu_wormhole_entry')]
#[Entity(repositoryClass: WormholeEntryRepository::class)]
class WormholeEntry
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string', length: 10, enumType: WormholeEntryTypeEnum::class)]
    private WormholeEntryTypeEnum $type = WormholeEntryTypeEnum::BOTH;

    #[Column(type: 'integer', nullable: true)]
    private ?int $last_used = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $cooldown = null;

    #[ManyToOne(targetEntity: Map::class, inversedBy: 'wormholeEntries')]
    #[JoinColumn(name: 'map_id', nullable: false, referencedColumnName: 'id')]
    private Map $map;

    #[ManyToOne(targetEntity: StarSystem::class)]
    #[JoinColumn(name: 'system_id', nullable: false, referencedColumnName: 'id')]
    private StarSystem $starSystem;

    #[ManyToOne(targetEntity: StarSystemMap::class, inversedBy: 'wormholeEntries')]
    #[JoinColumn(name: 'system_map_id', nullable: false, referencedColumnName: 'id')]
    private StarSystemMap $systemMap;

    /**
     * @var ArrayCollection<int, WormholeRestriction>
     */
    #[OneToMany(targetEntity: WormholeRestriction::class, mappedBy: 'wormholeEntry')]
    private Collection $restrictions;


    public function __construct()
    {
        $this->restrictions = new ArrayCollection();
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getMap(): Map
    {
        return $this->map;
    }

    public function getSystem(): StarSystem
    {
        return $this->starSystem;
    }

    public function getSystemMap(): StarSystemMap
    {
        return $this->systemMap;
    }

    public function setLastUsed(int $lastUsed): WormholeEntry
    {
        $this->last_used = $lastUsed;

        return $this;
    }

    public function isUsable(Location $location): bool
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

    /**
     * @return Collection<int, WormholeRestriction>
     */
    public function getRestrictions(): Collection
    {
        return $this->restrictions;
    }
}
