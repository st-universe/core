<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Stu\Orm\Repository\StarSystemMapRepository;

#[Table(name: 'stu_sys_map')]
#[UniqueConstraint(name: 'system_coordinates_idx', columns: ['sx', 'sy', 'systems_id'])]
#[Entity(repositoryClass: StarSystemMapRepository::class)]
class StarSystemMap extends Location
{
    #[Column(type: 'smallint')]
    private int $sx = 0;

    #[Column(type: 'smallint')]
    private int $sy = 0;

    #[Column(type: 'integer')]
    private int $systems_id = 0;

    #[ManyToOne(targetEntity: StarSystem::class, inversedBy: 'fields')]
    #[JoinColumn(name: 'systems_id', nullable: false, referencedColumnName: 'id')]
    private StarSystem $starSystem;

    #[OneToOne(targetEntity: Colony::class, mappedBy: 'starsystem_map')]
    private ?Colony $colony = null;

    /**
     * @var ArrayCollection<int, WormholeEntry>
     */
    #[OneToMany(targetEntity: WormholeEntry::class, mappedBy: 'systemMap')]
    private Collection $wormholeEntries;

    public function __construct()
    {
        parent::__construct();
        $this->wormholeEntries = new ArrayCollection();
    }

    public function getLayer(): ?Layer
    {
        return $this->layer;
    }

    public function getSx(): int
    {
        return $this->sx;
    }

    public function setSx(int $sx): StarSystemMap
    {
        $this->sx = $sx;

        return $this;
    }

    public function getX(): int
    {
        return $this->getSx();
    }

    public function getSy(): int
    {
        return $this->sy;
    }

    public function setSy(int $sy): StarSystemMap
    {
        $this->sy = $sy;

        return $this;
    }

    public function getY(): int
    {
        return $this->getSy();
    }

    public function getSystemId(): int
    {
        return $this->systems_id;
    }

    public function getSystem(): StarSystem
    {
        return $this->starSystem;
    }

    public function setSystem(StarSystem $starSystem): StarSystemMap
    {
        $this->starSystem = $starSystem;

        return $this;
    }

    public function getColony(): ?Colony
    {
        return $this->colony;
    }

    public function getMapRegion(): ?MapRegion
    {
        return null;
    }

    public function getAdministratedRegion(): ?MapRegion
    {
        return null;
    }

    public function getInfluenceArea(): ?StarSystem
    {
        return null;
    }

    protected function getWormholeEntries(): Collection
    {
        return $this->wormholeEntries;
    }

    public function getFieldStyle(): string
    {
        return "background-image: url('/assets/map/" . $this->getFieldId() . ".png'); opacity:1;";
    }

    public function getSectorId(): ?int
    {
        $parentMap = $this->getSystem()->getMap();
        if ($parentMap === null) {
            return null;
        }

        return $parentMap->getSectorId();
    }

    public function getSectorString(): string
    {
        return sprintf(
            '%d|%d (%s-%s)',
            $this->getSx(),
            $this->getSy(),
            $this->getSystem()->getName(),
            $this->getSystem()->isWormhole() ? 'Wurmloch' : 'System'
        );
    }
}
