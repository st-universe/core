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
use Doctrine\ORM\Mapping\Table;
use LogicException;
use Override;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Orm\Repository\SpacecraftRumpRepository;

#[Table(name: 'stu_rump')]
#[Entity(repositoryClass: SpacecraftRumpRepository::class)]
class SpacecraftRump implements SpacecraftRumpInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[OneToOne(targetEntity: SpacecraftRumpBaseValues::class, mappedBy: 'rump', fetch: 'EXTRA_LAZY', cascade: ['all'])]
    private ?SpacecraftRumpBaseValuesInterface $baseValues;

    #[column(type: 'integer', enumType: SpacecraftRumpCategoryEnum::class, nullable: false)]
    private SpacecraftRumpCategoryEnum $category_id;

    #[column(type: 'integer', enumType: SpacecraftRumpRoleEnum::class, nullable: true)]
    private ?SpacecraftRumpRoleEnum $role_id = null;

    #[column(type: 'smallint')]
    private int $phaser_volleys = 0;

    #[column(type: 'smallint')]
    private int $phaser_hull_damage_factor = 0;

    #[column(type: 'smallint')]
    private int $phaser_shield_damage_factor = 0;

    #[column(type: 'smallint')]
    private int $torpedo_level = 0;

    #[column(type: 'smallint')]
    private int $torpedo_volleys = 0;

    #[column(type: 'smallint')]
    private int $base_torpedo_storage = 0;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'boolean')]
    private bool $is_buildable;

    #[Column(type: 'boolean')]
    private bool $is_npc;

    #[column(type: 'smallint')]
    private int $eps_cost = 0;

    #[column(type: 'integer')]
    private int $storage = 0;

    #[column(type: 'smallint')]
    private int $slots = 0;

    #[column(type: 'integer')]
    private int $buildtime = 0;

    #[column(type: 'smallint', nullable: true)]
    private ?int $needed_workbees = null;

    #[column(type: 'smallint')]
    private int $sort = 0;

    #[column(type: 'integer', nullable: true)]
    private ?int $database_id = 0;

    #[column(type: 'integer', nullable: true)]
    private ?int $commodity_id = 0;

    #[column(type: 'smallint')]
    private int $flight_ecost = 0;

    #[column(type: 'smallint')]
    private int $beam_factor = 0;

    #[column(type: 'smallint')]
    private int $shuttle_slots = 0;

    #[column(type: 'integer')]
    private int $tractor_mass = 1;

    #[column(type: 'integer')]
    private int $tractor_payload = 100;

    #[Column(type: 'integer')]
    private int $prestige;

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $npc_buildable = true;

    #[ManyToOne(targetEntity: ShipRumpRole::class)]
    #[JoinColumn(name: 'role_id', referencedColumnName: 'id')]
    private ?ShipRumpRoleInterface $shipRumpRole = null;

    /**
     * @var ArrayCollection<int, ShipRumpSpecialInterface>
     */
    #[OneToMany(targetEntity: ShipRumpSpecial::class, mappedBy: 'spacecraftRump', indexBy: 'special')]
    private Collection $specialAbilities;

    #[ManyToOne(targetEntity: ShipRumpCategory::class)]
    #[JoinColumn(name: 'category_id', nullable: false, referencedColumnName: 'id')]
    private ShipRumpCategoryInterface $shipRumpCategory;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id')]
    private ?CommodityInterface $commodity = null;

    #[ManyToOne(targetEntity: DatabaseEntry::class)]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntryInterface $databaseEntry = null;

    /**
     * @var ArrayCollection<int, ShipRumpCostInterface>
     */
    #[OneToMany(targetEntity: ShipRumpCost::class, mappedBy: 'spacecraftRump')]
    private Collection $buildingCosts;

    /**
     * @var ArrayCollection<int, BuildplanHangarInterface>
     */
    #[OneToMany(targetEntity: BuildplanHangar::class, mappedBy: 'spacecraftRump')]
    private Collection $startHangar;

    public function __construct()
    {
        $this->buildingCosts = new ArrayCollection();
        $this->startHangar = new ArrayCollection();
        $this->specialAbilities = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getBaseValues(): SpacecraftRumpBaseValuesInterface
    {
        return $this->baseValues ?? throw new LogicException('Rump has no base balues');
    }

    #[Override]
    public function getCategoryId(): SpacecraftRumpCategoryEnum
    {
        return $this->category_id;
    }

    #[Override]
    public function getRoleId(): ?SpacecraftRumpRoleEnum
    {
        return $this->role_id;
    }

    #[Override]
    public function getBeamFactor(): int
    {
        return $this->beam_factor;
    }

    #[Override]
    public function getShuttleSlots(): int
    {
        return $this->shuttle_slots;
    }

    #[Override]
    public function getTractorMass(): int
    {
        return $this->tractor_mass;
    }

    #[Override]
    public function getTractorPayload(): int
    {
        return $this->tractor_payload;
    }

    #[Override]
    public function getPhaserVolleys(): int
    {
        return $this->phaser_volleys;
    }

    #[Override]
    public function getPhaserHullDamageFactor(): int
    {
        return $this->phaser_hull_damage_factor;
    }

    #[Override]
    public function getPhaserShieldDamageFactor(): int
    {
        return $this->phaser_shield_damage_factor;
    }

    #[Override]
    public function getTorpedoLevel(): int
    {
        return $this->torpedo_level;
    }

    #[Override]
    public function getTorpedoVolleys(): int
    {
        return $this->torpedo_volleys;
    }

    #[Override]
    public function getBaseTorpedoStorage(): int
    {
        return $this->base_torpedo_storage;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): SpacecraftRumpInterface
    {
        $this->name = $name;
        return $this;
    }

    #[Override]
    public function getIsBuildable(): bool
    {
        return $this->is_buildable;
    }

    #[Override]
    public function setIsBuildable(bool $isBuildable): SpacecraftRumpInterface
    {
        $this->is_buildable = $isBuildable;
        return $this;
    }

    #[Override]
    public function getIsNpc(): bool
    {
        return $this->is_npc;
    }

    #[Override]
    public function setIsNpc(bool $isNpc): SpacecraftRumpInterface
    {
        $this->is_npc = $isNpc;
        return $this;
    }

    #[Override]
    public function getEpsCost(): int
    {
        return $this->eps_cost;
    }

    #[Override]
    public function setEpsCost(int $energyCosts): SpacecraftRumpInterface
    {
        $this->eps_cost = $energyCosts;
        return $this;
    }

    #[Override]
    public function getStorage(): int
    {
        return $this->storage;
    }

    #[Override]
    public function setStorage(int $storage): SpacecraftRumpInterface
    {
        $this->storage = $storage;
        return $this;
    }

    #[Override]
    public function getDockingSlots(): int
    {
        return $this->slots;
    }

    #[Override]
    public function setDockingSlots(int $slots): SpacecraftRumpInterface
    {
        $this->slots = $slots;
        return $this;
    }

    #[Override]
    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    #[Override]
    public function setBuildtime(int $buildtime): SpacecraftRumpInterface
    {
        $this->buildtime = $buildtime;
        return $this;
    }

    #[Override]
    public function getSort(): int
    {
        return $this->sort;
    }

    #[Override]
    public function setSort(int $sort): SpacecraftRumpInterface
    {
        $this->sort = $sort;
        return $this;
    }

    #[Override]
    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    #[Override]
    public function getCommodityId(): ?int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function setCommodityId(?int $commodityId): SpacecraftRumpInterface
    {
        $this->commodity_id = $commodityId;
        return $this;
    }

    #[Override]
    public function getFlightEcost(): int
    {
        return $this->flight_ecost;
    }

    #[Override]
    public function getShipRumpRole(): ?ShipRumpRoleInterface
    {
        return $this->shipRumpRole;
    }

    #[Override]
    public function getShipRumpCategory(): ShipRumpCategoryInterface
    {
        return $this->shipRumpCategory;
    }

    #[Override]
    public function getCommodity(): ?CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function setCommodity(?CommodityInterface $commodity): SpacecraftRumpInterface
    {
        $this->commodity = $commodity;
        return $this;
    }

    #[Override]
    public function getNeededWorkbees(): ?int
    {
        return $this->needed_workbees;
    }

    #[Override]
    public function getNeededRepairWorkbees(): ?int
    {
        return  $this->getNeededWorkbees() / 5;
    }

    #[Override]
    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    #[Override]
    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): SpacecraftRumpInterface
    {
        $this->databaseEntry = $databaseEntry;
        return $this;
    }

    #[Override]
    public function getPrestige(): int
    {
        return $this->prestige;
    }

    #[Override]
    public function isEscapePods(): bool
    {
        return $this->getCategoryId() === SpacecraftRumpCategoryEnum::SHIP_CATEGORY_ESCAPE_PODS;
    }

    #[Override]
    public function isShipyard(): bool
    {
        return $this->getCategoryId() === SpacecraftRumpCategoryEnum::SHIP_CATEGORY_STATION
            && $this->getRoleId() === SpacecraftRumpRoleEnum::SHIP_ROLE_SHIPYARD;
    }

    #[Override]
    public function isStation(): bool
    {
        return $this->getCategoryId() === SpacecraftRumpCategoryEnum::SHIP_CATEGORY_STATION;
    }

    #[Override]
    public function isWorkbee(): bool
    {
        $commodity = $this->getCommodity();

        return $commodity !== null && $commodity->isWorkbee();
    }

    #[Override]
    public function getStartHangar(): Collection
    {
        return $this->startHangar;
    }

    #[Override]
    public function getBuildingCosts(): Collection
    {
        return $this->buildingCosts;
    }

    #[Override]
    public function hasSpecialAbility(int $value): bool
    {
        return $this->specialAbilities->containsKey($value);
    }

    #[Override]
    public function getFactionId(): int
    {
        //last digit of id shows faction id
        return $this->getId() % 10;
    }

    /**
     * @return Collection<int, ShipRumpSpecialInterface>
     */
    #[Override]
    public function getSpecialAbilities(): Collection
    {
        return $this->specialAbilities;
    }

    #[Override]
    public function getNpcBuildable(): ?bool
    {
        return $this->npc_buildable;
    }

    #[Override]
    public function setNpcBuildable(?bool $npcBuildable): SpacecraftRumpInterface
    {
        $this->npc_buildable = $npcBuildable;
        return $this;
    }
}
