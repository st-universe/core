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
use Override;
use Stu\Component\Colony\ColonyEnum;
use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Orm\Repository\ColonyClassRepository;

#[Table(name: 'stu_colonies_classes')]
#[Entity(repositoryClass: ColonyClassRepository::class)]
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

    #[OneToOne(targetEntity: DatabaseEntry::class)]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntryInterface $databaseEntry;

    /**
     * @var ArrayCollection<int, ColonyClassDepositInterface>
     */
    #[OneToMany(targetEntity: ColonyClassDeposit::class, mappedBy: 'colonyClass', indexBy: 'commodity_id')]
    private Collection $colonyClassDeposits;

    /**
     * @var ArrayCollection<int, ColonyClassRestrictionInterface>
     */
    #[OneToMany(mappedBy: 'colonyClass', targetEntity: ColonyClassRestriction::class)]
    private Collection $restrictions;


    public function __construct()
    {
        $this->colonyClassDeposits = new ArrayCollection();
        $this->restrictions = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): ColonyClassInterface
    {
        $this->name = $name;

        return $this;
    }

    #[Override]
    public function getType(): int
    {
        return $this->type;
    }

    #[Override]
    public function isPlanet(): bool
    {
        return $this->getType() === ColonyTypeEnum::COLONY_TYPE_PLANET;
    }

    #[Override]
    public function isMoon(): bool
    {
        return $this->getType() === ColonyTypeEnum::COLONY_TYPE_MOON;
    }

    #[Override]
    public function isAsteroid(): bool
    {
        return $this->getType() === ColonyTypeEnum::COLONY_TYPE_ASTEROID;
    }

    #[Override]
    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    #[Override]
    public function setDatabaseEntry(?DatabaseEntryInterface $entry): ColonyClassInterface
    {
        $this->databaseEntry = $entry;

        return $this;
    }

    #[Override]
    public function getColonizeableFields(): array
    {
        return $this->colonizeable_fields;
    }

    #[Override]
    public function setColonizeableFields(array $colonizeableFields): ColonyClassInterface
    {
        $this->colonizeable_fields = $colonizeableFields;

        return $this;
    }

    #[Override]
    public function getBevGrowthRate(): int
    {
        return $this->bev_growth_rate;
    }

    #[Override]
    public function setBevGrowthRate(int $bevGroethRate): ColonyClassInterface
    {
        $this->bev_growth_rate = $bevGroethRate;

        return $this;
    }

    #[Override]
    public function getSpecialId(): int
    {
        return $this->special;
    }

    #[Override]
    public function setSpecialId(int $specialId): ColonyClassInterface
    {
        $this->special = $specialId;

        return $this;
    }

    #[Override]
    public function getAllowStart(): bool
    {
        return $this->allow_start;
    }

    #[Override]
    public function setAllowStart(bool $allowStart): ColonyClassInterface
    {
        $this->allow_start = $allowStart;

        return $this;
    }

    #[Override]
    public function getColonyClassDeposits(): Collection
    {
        return $this->colonyClassDeposits;
    }

    #[Override]
    public function hasRing(): bool
    {
        return $this->getSpecialId() == ColonyEnum::COLONY_CLASS_SPECIAL_RING;
    }

    #[Override]
    public function getMinRotation(): int
    {
        return $this->min_rot;
    }

    #[Override]
    public function setMinRotation(int $rotation): ColonyClassInterface
    {
        $this->min_rot = $rotation;

        return $this;
    }

    #[Override]
    public function getMaxRotation(): int
    {
        return $this->max_rot;
    }

    #[Override]
    public function setMaxRotation(int $rotation): ColonyClassInterface
    {
        $this->max_rot = $rotation;

        return $this;
    }

    #[Override]
    public function getRestrictions(): Collection
    {
        return $this->restrictions;
    }
}
