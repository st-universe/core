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
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Orm\Repository\SpacecraftRumpRepository;

#[Table(name: 'stu_rump')]
#[Entity(repositoryClass: SpacecraftRumpRepository::class)]
class SpacecraftRump
{
    // spacecraft can colonize
    public const int SPECIAL_ABILITY_COLONIZE = 1;

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[OneToOne(targetEntity: SpacecraftRumpBaseValues::class, mappedBy: 'rump', fetch: 'EXTRA_LAZY', cascade: ['all'])]
    private ?SpacecraftRumpBaseValues $baseValues;

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
    private ?ShipRumpRole $shipRumpRole = null;

    /**
     * @var ArrayCollection<int, ShipRumpSpecial>
     */
    #[OneToMany(targetEntity: ShipRumpSpecial::class, mappedBy: 'spacecraftRump', indexBy: 'special')]
    private Collection $specialAbilities;

    #[ManyToOne(targetEntity: ShipRumpCategory::class)]
    #[JoinColumn(name: 'category_id', nullable: false, referencedColumnName: 'id')]
    private ShipRumpCategory $shipRumpCategory;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id')]
    private ?Commodity $commodity = null;

    #[ManyToOne(targetEntity: DatabaseEntry::class)]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntry $databaseEntry = null;

    /**
     * @var ArrayCollection<int, ShipRumpCost>
     */
    #[OneToMany(targetEntity: ShipRumpCost::class, mappedBy: 'spacecraftRump')]
    private Collection $buildingCosts;

    /**
     * @var ArrayCollection<int, BuildplanHangar>
     */
    #[OneToMany(targetEntity: BuildplanHangar::class, mappedBy: 'spacecraftRump')]
    private Collection $startHangar;

    public function __construct()
    {
        $this->buildingCosts = new ArrayCollection();
        $this->startHangar = new ArrayCollection();
        $this->specialAbilities = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBaseValues(): SpacecraftRumpBaseValues
    {
        return $this->baseValues ?? throw new LogicException('Rump has no base balues');
    }

    public function getCategoryId(): SpacecraftRumpCategoryEnum
    {
        return $this->category_id;
    }

    public function getRoleId(): ?SpacecraftRumpRoleEnum
    {
        return $this->role_id;
    }

    public function getBeamFactor(): int
    {
        return $this->beam_factor;
    }

    public function getShuttleSlots(): int
    {
        return $this->shuttle_slots;
    }

    public function getTractorMass(): int
    {
        return $this->tractor_mass;
    }

    public function getTractorPayload(): int
    {
        return $this->tractor_payload;
    }

    public function getPhaserVolleys(): int
    {
        return $this->phaser_volleys;
    }

    public function getPhaserHullDamageFactor(): int
    {
        return $this->phaser_hull_damage_factor;
    }

    public function getPhaserShieldDamageFactor(): int
    {
        return $this->phaser_shield_damage_factor;
    }

    public function getTorpedoLevel(): int
    {
        return $this->torpedo_level;
    }

    public function getTorpedoVolleys(): int
    {
        return $this->torpedo_volleys;
    }

    public function getBaseTorpedoStorage(): int
    {
        return $this->base_torpedo_storage;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): SpacecraftRump
    {
        $this->name = $name;
        return $this;
    }

    public function getIsBuildable(): bool
    {
        return $this->is_buildable;
    }

    public function setIsBuildable(bool $isBuildable): SpacecraftRump
    {
        $this->is_buildable = $isBuildable;
        return $this;
    }

    public function getIsNpc(): bool
    {
        return $this->is_npc;
    }

    public function setIsNpc(bool $isNpc): SpacecraftRump
    {
        $this->is_npc = $isNpc;
        return $this;
    }

    public function getEpsCost(): int
    {
        return $this->eps_cost;
    }

    public function setEpsCost(int $energyCosts): SpacecraftRump
    {
        $this->eps_cost = $energyCosts;
        return $this;
    }

    public function getStorage(): int
    {
        return $this->storage;
    }

    public function setStorage(int $storage): SpacecraftRump
    {
        $this->storage = $storage;
        return $this;
    }

    public function getDockingSlots(): int
    {
        return $this->slots;
    }

    public function setDockingSlots(int $slots): SpacecraftRump
    {
        $this->slots = $slots;
        return $this;
    }

    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    public function setBuildtime(int $buildtime): SpacecraftRump
    {
        $this->buildtime = $buildtime;
        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): SpacecraftRump
    {
        $this->sort = $sort;
        return $this;
    }

    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    public function getCommodityId(): ?int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(?int $commodityId): SpacecraftRump
    {
        $this->commodity_id = $commodityId;
        return $this;
    }

    public function getFlightEcost(): int
    {
        return $this->flight_ecost;
    }

    public function getShipRumpRole(): ?ShipRumpRole
    {
        return $this->shipRumpRole;
    }

    public function getShipRumpCategory(): ShipRumpCategory
    {
        return $this->shipRumpCategory;
    }

    public function getCommodity(): ?Commodity
    {
        return $this->commodity;
    }

    public function setCommodity(?Commodity $commodity): SpacecraftRump
    {
        $this->commodity = $commodity;
        return $this;
    }

    public function getNeededWorkbees(): ?int
    {
        return $this->needed_workbees;
    }

    public function getNeededRepairWorkbees(): ?int
    {
        return  $this->getNeededWorkbees() / 5;
    }

    public function getDatabaseEntry(): ?DatabaseEntry
    {
        return $this->databaseEntry;
    }

    public function setDatabaseEntry(?DatabaseEntry $databaseEntry): SpacecraftRump
    {
        $this->databaseEntry = $databaseEntry;
        return $this;
    }

    public function getPrestige(): int
    {
        return $this->prestige;
    }

    public function isEscapePods(): bool
    {
        return $this->getCategoryId() === SpacecraftRumpCategoryEnum::ESCAPE_PODS;
    }

    public function isShipyard(): bool
    {
        return $this->getCategoryId() === SpacecraftRumpCategoryEnum::STATION
            && $this->getRoleId() === SpacecraftRumpRoleEnum::SHIPYARD;
    }

    public function isStation(): bool
    {
        return $this->getCategoryId() === SpacecraftRumpCategoryEnum::STATION;
    }

    public function isWorkbee(): bool
    {
        $commodity = $this->getCommodity();

        return $commodity !== null && $commodity->isWorkbee();
    }

    /**
     * @return Collection<int, BuildplanHangar>
     */
    public function getStartHangar(): Collection
    {
        return $this->startHangar;
    }

    /**
     * @return Collection<int, ShipRumpCost>
     */
    public function getBuildingCosts(): Collection
    {
        return $this->buildingCosts;
    }

    public function hasSpecialAbility(int $value): bool
    {
        return $this->specialAbilities->containsKey($value);
    }

    public function getFactionId(): int
    {
        //last digit of id shows faction id
        return $this->getId() % 10;
    }

    /**
     * @return Collection<int, ShipRumpSpecial>
     */
    public function getSpecialAbilities(): Collection
    {
        return $this->specialAbilities;
    }

    public function getNpcBuildable(): ?bool
    {
        return $this->npc_buildable;
    }

    public function setNpcBuildable(?bool $npcBuildable): SpacecraftRump
    {
        $this->npc_buildable = $npcBuildable;
        return $this;
    }
}
