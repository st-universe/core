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
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Stu\Module\Ship\Lib\EntityWithAstroEntryInterface;
use Stu\Orm\Repository\StarSystemRepository;

#[Table(name: 'stu_systems')]
#[Entity(repositoryClass: StarSystemRepository::class)]
class StarSystem implements EntityWithAstroEntryInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $type = 0;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'smallint')]
    private int $max_x = 0;

    #[Column(type: 'smallint')]
    private int $max_y = 0;

    #[Column(type: 'smallint')]
    private int $bonus_fields = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $database_id = 0;

    #[Column(type: 'boolean')]
    private bool $is_wormhole = false;

    #[ManyToOne(targetEntity: StarSystemType::class)]
    #[JoinColumn(name: 'type', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private StarSystemType $systemType;

    #[OneToOne(targetEntity: Map::class, mappedBy: 'starSystem')]
    private ?Map $map = null;

    #[ManyToOne(targetEntity: DatabaseEntry::class)]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?DatabaseEntry $databaseEntry = null;

    #[OneToOne(targetEntity: Station::class, mappedBy: 'influenceArea')]
    private ?Station $station = null;

    /**
     * @var ArrayCollection<int, StarSystemMap>
     */
    #[OneToMany(targetEntity: StarSystemMap::class, mappedBy: 'starSystem')]
    #[OrderBy(['sy' => 'ASC', 'sx' => 'ASC'])]
    private Collection $fields;

    /** @var ArrayCollection<int, AstronomicalEntry> */
    #[OneToMany(targetEntity: AstronomicalEntry::class, mappedBy: 'starSystem', indexBy: 'user_id', fetch: 'EXTRA_LAZY')]
    private Collection $astronomicalEntries;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->astronomicalEntries = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCx(): ?int
    {
        $map = $this->map;
        if ($map !== null) {
            return $map->getCx();
        }

        return null;
    }

    public function getCy(): ?int
    {
        $map = $this->map;
        if ($map !== null) {
            return $map->getCy();
        }

        return null;
    }

    public function getType(): StarSystemType
    {
        return $this->systemType;
    }

    public function setType(StarSystemType $systemType): StarSystem
    {
        $this->systemType = $systemType;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): StarSystem
    {
        $this->name = $name;

        return $this;
    }

    public function getMaxX(): int
    {
        return $this->max_x;
    }

    public function setMaxX(int $maxX): StarSystem
    {
        $this->max_x = $maxX;

        return $this;
    }

    public function getMaxY(): int
    {
        return $this->max_y;
    }

    public function setMaxY(int $maxY): StarSystem
    {
        $this->max_y = $maxY;

        return $this;
    }

    public function getBonusFieldAmount(): int
    {
        return $this->bonus_fields;
    }

    public function setBonusFieldAmount(int $bonusFieldAmount): StarSystem
    {
        $this->bonus_fields = $bonusFieldAmount;

        return $this;
    }

    public function getSystemType(): StarSystemType
    {
        return $this->systemType;
    }

    public function getDatabaseEntry(): ?DatabaseEntry
    {
        return $this->databaseEntry;
    }

    public function setDatabaseEntry(?DatabaseEntry $databaseEntry): StarSystem
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    public function getLayer(): ?Layer
    {
        if ($this->isWormhole()) {
            return null;
        }

        return $this->getMap()?->getLayer();
    }

    public function getMap(): ?Map
    {
        return $this->map;
    }

    public function getStation(): ?Station
    {
        return $this->station;
    }

    public function unsetStation(): void
    {
        $this->station = null;
    }

    /**
     * @return Collection<int, StarSystemMap>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function isWormhole(): bool
    {
        return $this->is_wormhole;
    }

    public function getAstronomicalEntries(): Collection
    {
        return $this->astronomicalEntries;
    }
}
