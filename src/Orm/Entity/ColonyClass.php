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
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Colony\ColonyEnum;
use Stu\Component\Colony\ColonyTypeEnum;

#[Table(name: 'stu_colonies_classes')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ColonyClassRepository')]
class ColonyClass implements ColonyClassInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer')]
    private int $type;

    #[Column(type: 'integer', nullable: true)]
    private ?int $database_id = null;

    /**
     * @var array<int>
     */
    #[Column(type: 'json')]
    private array $colonizeable_fields = [];

    #[Column(type: 'smallint')]
    private int $bev_growth_rate = 0;

    #[Column(type: 'smallint')]
    private int $special = 0;

    #[Column(type: 'boolean')]
    private bool $allow_start = false;

    #[Column(type: 'integer')]
    private int $min_rot = 1;

    #[Column(type: 'integer')]
    private int $max_rot = 1;

    /**
     * @var DatabaseEntryInterface|null
     */
    #[OneToOne(targetEntity: 'DatabaseEntry')]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private $databaseEntry;

    /**
     * @var ArrayCollection<int, ColonyClassDepositInterface>
     */
    #[OneToMany(targetEntity: 'ColonyClassDeposit', mappedBy: 'colonyClass', indexBy: 'commodity_id')]
    private Collection $colonyClassDeposits;

    public function __construct()
    {
        $this->colonyClassDeposits = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ColonyClassInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function isPlanet(): bool
    {
        return $this->getType() === ColonyTypeEnum::COLONY_TYPE_PLANET;
    }

    public function isMoon(): bool
    {
        return $this->getType() === ColonyTypeEnum::COLONY_TYPE_MOON;
    }

    public function isAsteroid(): bool
    {
        return $this->getType() === ColonyTypeEnum::COLONY_TYPE_ASTEROID;
    }

    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    public function setDatabaseId(?int $databaseId): ColonyClassInterface
    {
        $this->database_id = $databaseId;

        return $this;
    }

    public function getColonizeableFields(): array
    {
        return $this->colonizeable_fields;
    }

    public function setColonizeableFields(array $colonizeableFields): ColonyClassInterface
    {
        $this->colonizeable_fields = $colonizeableFields;

        return $this;
    }

    public function getBevGrowthRate(): int
    {
        return $this->bev_growth_rate;
    }

    public function setBevGrowthRate(int $bevGroethRate): ColonyClassInterface
    {
        $this->bev_growth_rate = $bevGroethRate;

        return $this;
    }

    public function getSpecialId(): int
    {
        return $this->special;
    }

    public function setSpecialId(int $specialId): ColonyClassInterface
    {
        $this->special = $specialId;

        return $this;
    }

    public function getAllowStart(): bool
    {
        return $this->allow_start;
    }

    public function setAllowStart(bool $allowStart): ColonyClassInterface
    {
        $this->allow_start = $allowStart;

        return $this;
    }

    public function getColonyClassDeposits(): Collection
    {
        return $this->colonyClassDeposits;
    }

    public function hasRing(): bool
    {
        return $this->getSpecialId() == ColonyEnum::COLONY_CLASS_SPECIAL_RING;
    }

    public function getMinRotation(): int
    {
        return $this->min_rot;
    }

    public function setMinRotation(int $rotation): ColonyClassInterface
    {
        $this->min_rot = $rotation;

        return $this;
    }

    public function getMaxRotation(): int
    {
        return $this->max_rot;
    }

    public function setMaxRotation(int $rotation): ColonyClassInterface
    {
        $this->max_rot = $rotation;

        return $this;
    }
}
