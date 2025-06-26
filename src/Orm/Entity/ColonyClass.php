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
use Stu\Orm\Repository\ColonyClassRepository;

#[Table(name: 'stu_colonies_classes')]
#[Entity(repositoryClass: ColonyClassRepository::class)]
class ColonyClass
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
    private ?DatabaseEntry $databaseEntry;

    /**
     * @var ArrayCollection<int, ColonyClassDeposit>
     */
    #[OneToMany(targetEntity: ColonyClassDeposit::class, mappedBy: 'colonyClass', indexBy: 'commodity_id')]
    private Collection $colonyClassDeposits;

    /**
     * @var ArrayCollection<int, ColonyClassRestriction>
     */
    #[OneToMany(mappedBy: 'colonyClass', targetEntity: ColonyClassRestriction::class)]
    private Collection $restrictions;


    public function __construct()
    {
        $this->colonyClassDeposits = new ArrayCollection();
        $this->restrictions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ColonyClass
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

    public function setDatabaseEntry(?DatabaseEntry $entry): ColonyClass
    {
        $this->databaseEntry = $entry;

        return $this;
    }

    /**
     * @return array<int>
     */
    public function getColonizeableFields(): array
    {
        return $this->colonizeable_fields;
    }

    /**
     * @param array<int> $colonizeableFields
     */
    public function setColonizeableFields(array $colonizeableFields): ColonyClass
    {
        $this->colonizeable_fields = $colonizeableFields;

        return $this;
    }

    public function getBevGrowthRate(): int
    {
        return $this->bev_growth_rate;
    }

    public function setBevGrowthRate(int $bevGroethRate): ColonyClass
    {
        $this->bev_growth_rate = $bevGroethRate;

        return $this;
    }

    public function getSpecialId(): int
    {
        return $this->special;
    }

    public function setSpecialId(int $specialId): ColonyClass
    {
        $this->special = $specialId;

        return $this;
    }

    public function getAllowStart(): bool
    {
        return $this->allow_start;
    }

    public function setAllowStart(bool $allowStart): ColonyClass
    {
        $this->allow_start = $allowStart;

        return $this;
    }

    /**
     * @return Collection<int, ColonyClassDeposit>
     */
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

    public function setMinRotation(int $rotation): ColonyClass
    {
        $this->min_rot = $rotation;

        return $this;
    }

    public function getMaxRotation(): int
    {
        return $this->max_rot;
    }

    public function setMaxRotation(int $rotation): ColonyClass
    {
        $this->max_rot = $rotation;

        return $this;
    }

    /**
     * @return Collection<int, ColonyClassRestriction>
     */
    public function getRestrictions(): Collection
    {
        return $this->restrictions;
    }
}
