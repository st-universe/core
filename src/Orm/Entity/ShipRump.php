<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
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
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Ship\ShipRumpEnum;

#[Table(name: 'stu_rumps')]
#[Index(name: 'rump_category_idx', columns: ['category_id'])]
#[Index(name: 'rump_role_idx', columns: ['role_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ShipRumpRepository')]
class ShipRump implements ShipRumpInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[column(type: 'integer')]
    private int $category_id;

    #[column(type: 'integer', nullable: true)]
    private ?int $role_id = 0;

    #[column(type: 'smallint')]
    private int $evade_chance = 0;

    #[column(type: 'smallint')]
    private int $hit_chance = 0;

    #[column(type: 'smallint')]
    private int $module_level = 0;

    #[column(type: 'smallint')]
    private int $base_crew = 0;

    #[column(type: 'smallint')]
    private int $base_eps = 0;

    #[column(type: 'smallint')]
    private int $base_reactor = 0;

    #[column(type: 'integer')]
    private int $base_hull = 0;

    #[column(type: 'integer')]
    private int $base_shield = 0;

    #[column(type: 'smallint')]
    private int $base_damage = 0;

    #[column(type: 'smallint')]
    private int $base_sensor_range = 0;

    #[column(type: 'smallint')]
    private int $base_torpedo_storage = 0;

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
    private int $special_slots = 0;

    #[column(type: 'smallint')]
    private int $shuttle_slots = 0;

    #[column(type: 'integer')]
    private int $tractor_mass = 1;

    #[column(type: 'integer')]
    private int $tractor_payload = 100;

    #[Column(type: 'integer')]
    private int $prestige;

    #[Column(type: 'integer', nullable: true)]
    private ?int $base_warpdrive = 0;

    #[ManyToOne(targetEntity: 'ShipRumpRole')]
    #[JoinColumn(name: 'role_id', referencedColumnName: 'id')]
    private ?ShipRumpRoleInterface $shipRumpRole = null;

    /**
     * @var Collection<int, ShipRumpSpecialInterface>
     */
    #[OneToMany(targetEntity: 'ShipRumpSpecial', mappedBy: 'shipRump', indexBy: 'special')]
    private Collection $specialAbilities;

    #[ManyToOne(targetEntity: 'ShipRumpCategory')]
    #[JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    private ShipRumpCategoryInterface $shipRumpCategory;

    #[ManyToOne(targetEntity: 'Commodity')]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id')]
    private ?CommodityInterface $commodity = null;

    #[ManyToOne(targetEntity: 'DatabaseEntry')]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntryInterface $databaseEntry = null;

    /**
     * @var ArrayCollection<int, ShipRumpCostInterface>
     */
    #[OneToMany(targetEntity: 'ShipRumpCost', mappedBy: 'shipRump')]
    private Collection $buildingCosts;

    /**
     * @var ArrayCollection<int, BuildplanHangarInterface>
     */
    #[OneToMany(targetEntity: 'BuildplanHangar', mappedBy: 'shipRump')]
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
    public function setCategoryId(int $categoryId): ShipRumpInterface
    {
        $this->category_id = $categoryId;

        return $this;
    }

    #[Override]
    public function getCategoryId(): int
    {
        return $this->category_id;
    }

    #[Override]
    public function getRoleId(): ?int
    {
        return $this->role_id;
    }

    #[Override]
    public function setRoleId(?int $roleId): ShipRumpInterface
    {
        $this->role_id = $roleId;
        return $this;
    }

    #[Override]
    public function getEvadeChance(): int
    {
        return $this->evade_chance;
    }

    #[Override]
    public function setEvadeChance(int $evadeChance): ShipRumpInterface
    {
        $this->evade_chance = $evadeChance;
        return $this;
    }

    #[Override]
    public function getHitChance(): int
    {
        return $this->hit_chance;
    }

    #[Override]
    public function setHitChance(int $hitChance): ShipRumpInterface
    {
        $this->hit_chance = $hitChance;
        return $this;
    }

    #[Override]
    public function getModuleLevel(): int
    {
        return $this->module_level;
    }

    #[Override]
    public function setModuleLevel(int $moduleLevel): ShipRumpInterface
    {
        $this->module_level = $moduleLevel;
        return $this;
    }

    #[Override]
    public function getBaseCrew(): int
    {
        return $this->base_crew;
    }

    #[Override]
    public function setBaseCrew(int $baseCrew): ShipRumpInterface
    {
        $this->base_crew = $baseCrew;
        return $this;
    }

    #[Override]
    public function getBaseEps(): int
    {
        return $this->base_eps;
    }

    #[Override]
    public function setBaseEps(int $baseEps): ShipRumpInterface
    {
        $this->base_eps = $baseEps;
        return $this;
    }

    #[Override]
    public function getBaseReactor(): int
    {
        return $this->base_reactor;
    }

    #[Override]
    public function setBaseReactor(int $baseReactor): ShipRumpInterface
    {
        $this->base_reactor = $baseReactor;
        return $this;
    }

    #[Override]
    public function getBaseHull(): int
    {
        return $this->base_hull;
    }

    #[Override]
    public function setBaseHull(int $baseHull): ShipRumpInterface
    {
        $this->base_hull = $baseHull;
        return $this;
    }

    #[Override]
    public function getBaseShield(): int
    {
        return $this->base_shield;
    }

    #[Override]
    public function setBaseShield(int $baseShield): ShipRumpInterface
    {
        $this->base_shield = $baseShield;
        return $this;
    }

    #[Override]
    public function getBaseDamage(): int
    {
        return $this->base_damage;
    }

    #[Override]
    public function setBaseDamage(int $baseDamage): ShipRumpInterface
    {
        $this->base_damage = $baseDamage;
        return $this;
    }

    #[Override]
    public function getBaseSensorRange(): int
    {
        return $this->base_sensor_range;
    }

    #[Override]
    public function setBaseSensorRange(int $baseSensorRange): ShipRumpInterface
    {
        $this->base_sensor_range = $baseSensorRange;
        return $this;
    }

    #[Override]
    public function getBaseTorpedoStorage(): int
    {
        return $this->base_torpedo_storage;
    }

    #[Override]
    public function setBaseTorpedoStorage(int $baseTorpedoStorage): ShipRumpInterface
    {
        $this->base_torpedo_storage = $baseTorpedoStorage;
        return $this;
    }

    #[Override]
    public function getBeamFactor(): int
    {
        return $this->beam_factor;
    }

    #[Override]
    public function setBeamFactor(int $beamFactor): ShipRumpInterface
    {
        $this->beam_factor = $beamFactor;
        return $this;
    }

    #[Override]
    public function getSpecialSlots(): int
    {
        return $this->special_slots;
    }

    #[Override]
    public function setSpecialSlots(int $specialSlots): ShipRumpInterface
    {
        $this->special_slots = $specialSlots;
        return $this;
    }

    #[Override]
    public function getShuttleSlots(): int
    {
        return $this->shuttle_slots;
    }

    #[Override]
    public function setShuttleSlots(int $shuttleSlots): ShipRumpInterface
    {
        $this->shuttle_slots = $shuttleSlots;
        return $this;
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
    public function getBaseWarpDrive(): int
    {
        return $this->base_warpdrive;
    }

    #[Override]
    public function setBaseWarpDrive(int $baseWarpDrive): ShipRumpInterface
    {
        $this->base_warpdrive = $baseWarpDrive;
        return $this;
    }

    #[Override]
    public function getPhaserVolleys(): int
    {
        return $this->phaser_volleys;
    }

    #[Override]
    public function setPhaserVolleys(int $phaserVolleys): ShipRumpInterface
    {
        $this->phaser_volleys = $phaserVolleys;
        return $this;
    }

    #[Override]
    public function getPhaserHullDamageFactor(): int
    {
        return $this->phaser_hull_damage_factor;
    }

    #[Override]
    public function setPhaserHullDamageFactor(int $phaserHullDamageFactor): ShipRumpInterface
    {
        $this->phaser_hull_damage_factor = $phaserHullDamageFactor;
        return $this;
    }

    #[Override]
    public function getPhaserShieldDamageFactor(): int
    {
        return $this->phaser_shield_damage_factor;
    }

    #[Override]
    public function setPhaserShieldDamageFactor(int $phaserShieldDamageFactor): ShipRumpInterface
    {
        $this->phaser_shield_damage_factor = $phaserShieldDamageFactor;
        return $this;
    }

    #[Override]
    public function getTorpedoLevel(): int
    {
        return $this->torpedo_level;
    }

    #[Override]
    public function setTorpedoLevel(int $torpedoLevel): ShipRumpInterface
    {
        $this->torpedo_level = $torpedoLevel;
        return $this;
    }

    #[Override]
    public function getTorpedoVolleys(): int
    {
        return $this->torpedo_volleys;
    }

    #[Override]
    public function setTorpedoVolleys(int $torpedoVolleys): ShipRumpInterface
    {
        $this->torpedo_volleys = $torpedoVolleys;
        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): ShipRumpInterface
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
    public function setIsBuildable(bool $isBuildable): ShipRumpInterface
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
    public function setIsNpc(bool $isNpc): ShipRumpInterface
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
    public function setEpsCost(int $energyCosts): ShipRumpInterface
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
    public function setStorage(int $storage): ShipRumpInterface
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
    public function setDockingSlots(int $slots): ShipRumpInterface
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
    public function setBuildtime(int $buildtime): ShipRumpInterface
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
    public function setSort(int $sort): ShipRumpInterface
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
    public function setDatabaseId(?int $databaseId): ShipRumpInterface
    {
        $this->database_id = $databaseId;
        return $this;
    }

    #[Override]
    public function getCommodityId(): ?int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function setCommodityId(?int $commodityId): ShipRumpInterface
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
    public function setFlightEcost(int $flightEcost): ShipRumpInterface
    {
        $this->flight_ecost = $flightEcost;
        return $this;
    }

    #[Override]
    public function getShipRumpRole(): ?ShipRumpRoleInterface
    {
        return $this->shipRumpRole;
    }

    #[Override]
    public function setShipRumpRole(?ShipRumpRoleInterface $shipRumpRole): ShipRumpInterface
    {
        $this->shipRumpRole = $shipRumpRole;
        return $this;
    }

    #[Override]
    public function getShipRumpCategory(): ShipRumpCategoryInterface
    {
        return $this->shipRumpCategory;
    }

    #[Override]
    public function setShipRumpCategory(ShipRumpCategoryInterface $shipRumpCategory): ShipRumpInterface
    {
        $this->shipRumpCategory = $shipRumpCategory;
        return $this;
    }

    #[Override]
    public function getCommodity(): ?CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function setCommodity(?CommodityInterface $commodity): ShipRumpInterface
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
    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): ShipRumpInterface
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
    public function isTrumfield(): bool
    {
        return $this->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_DEBRISFIELD;
    }

    #[Override]
    public function isEscapePods(): bool
    {
        return $this->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_ESCAPE_PODS;
    }

    #[Override]
    public function isShipyard(): bool
    {
        return $this->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_STATION
            && $this->getRoleId() === ShipRumpEnum::SHIP_ROLE_SHIPYARD;
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
    public function getFractionId(): int
    {
        //last digit of id shows fraction id
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
}
