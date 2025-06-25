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
use Override;
use Stu\Orm\Repository\StarSystemMapRepository;

#[Table(name: 'stu_sys_map')]
#[UniqueConstraint(name: 'system_coordinates_idx', columns: ['sx', 'sy', 'systems_id'])]
#[Entity(repositoryClass: StarSystemMapRepository::class)]
class StarSystemMap extends Location implements StarSystemMapInterface
{
    #[Column(type: 'smallint')]
    private int $sx = 0;

    #[Column(type: 'smallint')]
    private int $sy = 0;

    #[Column(type: 'integer')]
    private int $systems_id = 0;

    #[ManyToOne(targetEntity: StarSystem::class, inversedBy: 'fields')]
    #[JoinColumn(name: 'systems_id', nullable: false, referencedColumnName: 'id')]
    private StarSystemInterface $starSystem;

    #[OneToOne(targetEntity: Colony::class, mappedBy: 'starsystem_map')]
    private ?ColonyInterface $colony = null;

    /**
     * @var ArrayCollection<int, WormholeEntryInterface>
     */
    #[OneToMany(targetEntity: WormholeEntry::class, mappedBy: 'systemMap')]
    private Collection $wormholeEntries;

    public function __construct()
    {
        parent::__construct();
        $this->wormholeEntries = new ArrayCollection();
    }

    #[Override]
    public function getLayer(): ?LayerInterface
    {
        return $this->layer;
    }

    #[Override]
    public function getSx(): int
    {
        return $this->sx;
    }

    #[Override]
    public function setSx(int $sx): StarSystemMapInterface
    {
        $this->sx = $sx;

        return $this;
    }

    #[Override]
    public function getX(): int
    {
        return $this->getSx();
    }

    #[Override]
    public function getSy(): int
    {
        return $this->sy;
    }

    #[Override]
    public function setSy(int $sy): StarSystemMapInterface
    {
        $this->sy = $sy;

        return $this;
    }

    #[Override]
    public function getY(): int
    {
        return $this->getSy();
    }

    #[Override]
    public function getSystemId(): int
    {
        return $this->systems_id;
    }

    #[Override]
    public function getSystem(): StarSystemInterface
    {
        return $this->starSystem;
    }

    #[Override]
    public function setSystem(StarSystemInterface $starSystem): StarSystemMapInterface
    {
        $this->starSystem = $starSystem;

        return $this;
    }

    #[Override]
    public function getColony(): ?ColonyInterface
    {
        return $this->colony;
    }

    #[Override]
    public function getMapRegion(): ?MapRegionInterface
    {
        return null;
    }

    #[Override]
    public function getAdministratedRegion(): ?MapRegionInterface
    {
        return null;
    }

    #[Override]
    public function getInfluenceArea(): ?StarSystemInterface
    {
        return null;
    }

    #[Override]
    protected function getWormholeEntries(): Collection
    {
        return $this->wormholeEntries;
    }

    #[Override]
    public function getFieldStyle(): string
    {
        return "background-image: url('/assets/map/" . $this->getFieldId() . ".png'); opacity:1;";
    }

    #[Override]
    public function getSectorId(): ?int
    {
        $parentMap = $this->getSystem()->getMap();
        if ($parentMap === null) {
            return null;
        }

        return $parentMap->getSectorId();
    }

    #[Override]
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
