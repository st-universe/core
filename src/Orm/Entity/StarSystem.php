<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\StarSystemRepository;

#[Table(name: 'stu_systems')]
#[Index(name: 'coordinate_idx', columns: ['cx', 'cy'])]
#[Entity(repositoryClass: StarSystemRepository::class)]
class StarSystem implements StarSystemInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    //TODO remove cx and cy
    #[Column(type: 'smallint', nullable: true)]
    private ?int $cx = null;

    #[Column(type: 'smallint', nullable: true)]
    private ?int $cy = null;

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

    #[ManyToOne(targetEntity: 'StarSystemType')]
    #[JoinColumn(name: 'type', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private StarSystemTypeInterface $systemType;

    #[OneToOne(targetEntity: 'Map', mappedBy: 'starSystem')]
    private ?MapInterface $map = null;

    #[ManyToOne(targetEntity: 'DatabaseEntry')]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?DatabaseEntryInterface $databaseEntry = null;

    #[OneToOne(targetEntity: 'Ship', mappedBy: 'influenceArea')]
    private ?ShipInterface $base = null;

    /**
     * @var Collection<int, StarSystemMapInterface>
     */
    #[OneToMany(targetEntity: 'StarSystemMap', mappedBy: 'starSystem')]
    #[OrderBy(['sy' => 'ASC', 'sx' => 'ASC'])]
    private Collection $fields;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getCx(): ?int
    {
        $map = $this->map;
        if ($map !== null) {
            return $map->getCx();
        }

        return null;
    }

    #[Override]
    public function getCy(): ?int
    {
        $map = $this->map;
        if ($map !== null) {
            return $map->getCy();
        }

        return null;
    }

    #[Override]
    public function getType(): StarSystemTypeInterface
    {
        return $this->systemType;
    }

    #[Override]
    public function setType(StarSystemTypeInterface $systemType): StarSystemInterface
    {
        $this->systemType = $systemType;

        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): StarSystemInterface
    {
        $this->name = $name;

        return $this;
    }

    #[Override]
    public function getMaxX(): int
    {
        return $this->max_x;
    }

    #[Override]
    public function setMaxX(int $maxX): StarSystemInterface
    {
        $this->max_x = $maxX;

        return $this;
    }

    #[Override]
    public function getMaxY(): int
    {
        return $this->max_y;
    }

    #[Override]
    public function setMaxY(int $maxY): StarSystemInterface
    {
        $this->max_y = $maxY;

        return $this;
    }

    #[Override]
    public function getBonusFieldAmount(): int
    {
        return $this->bonus_fields;
    }

    #[Override]
    public function setBonusFieldAmount(int $bonusFieldAmount): StarSystemInterface
    {
        $this->bonus_fields = $bonusFieldAmount;

        return $this;
    }

    #[Override]
    public function getSystemType(): StarSystemTypeInterface
    {
        return $this->systemType;
    }

    #[Override]
    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    #[Override]
    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): StarSystemInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    #[Override]
    public function getLayer(): ?LayerInterface
    {
        if ($this->isWormhole()) {
            return null;
        }

        return $this->getMap()->getLayer();
    }

    #[Override]
    public function getMap(): ?MapInterface
    {
        return $this->map;
    }

    #[Override]
    public function getBase(): ?ShipInterface
    {
        return $this->base;
    }

    #[Override]
    public function getFields(): Collection
    {
        return $this->fields;
    }

    #[Override]
    public function isWormhole(): bool
    {
        return $this->is_wormhole;
    }
}
