<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\ShipRumpSpecialRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRumpRepository")
 * @Table(
 *     name="stu_rumps",
 *     indexes={
 *          @Index(name="rump_category_idx", columns={"category_id"})
 *     }
 * )
 **/
class ShipRump implements ShipRumpInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @column(type="integer", nullable=true) * */
    private $category_id = 0;

    /** @column(type="integer", nullable=true) * */
    private $role_id = 0;

    /** @column(type="smallint") * */
    private $evade_chance = 0;

    /** @column(type="smallint") * */
    private $hit_chance = 0;

    /** @column(type="smallint") * */
    private $module_level = 0;

    /** @column(type="smallint") * */
    private $base_crew = 0;

    /** @column(type="smallint") * */
    private $base_eps = 0;

    /** @column(type="smallint") * */
    private $base_reactor = 0;

    /** @column(type="integer") * */
    private $base_hull = 0;

    /** @column(type="integer") * */
    private $base_shield = 0;

    /** @column(type="smallint") * */
    private $base_damage = 0;

    /** @column(type="smallint") * */
    private $base_sensor_range = 0;

    /** @column(type="smallint") * */
    private $base_torpedo_storage = 0;

    /** @column(type="smallint") * */
    private $phaser_volleys = 0;

    /** @column(type="smallint") * */
    private $phaser_hull_damage_factor = 0;

    /** @column(type="smallint") * */
    private $phaser_shield_damage_factor = 0;

    /** @column(type="smallint") * */
    private $torpedo_level = 0;

    /** @column(type="smallint") * */
    private $torpedo_volleys = 0;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="boolean") */
    private $is_buildable;

    /** @Column(type="boolean") */
    private $is_npc;

    /** @column(type="smallint") * */
    private $eps_cost = 0;

    /** @column(type="integer") * */
    private $storage = 0;

    /** @column(type="smallint") * */
    private $slots = 0;

    /** @column(type="integer") * */
    private $buildtime = 0;

    /** @column(type="smallint", nullable=true) * */
    private $needed_workbees;

    /** @column(type="smallint") * */
    private $sort = 0;

    /** @column(type="integer", nullable=true) * */
    private $database_id = 0;

    /** @column(type="integer", nullable=true) * */
    private $good_id = 0;

    /** @column(type="smallint") * */
    private $flight_ecost = 0;

    /** @column(type="smallint") * */
    private $beam_factor = 0;

    /** @column(type="smallint") * */
    private $special_slots = 0;

    /** @column(type="smallint") * */
    private $shuttle_slots = 0;

    /** @column(type="integer") * */
    private $tractor_mass = 1;

    /** @column(type="integer") * */
    private $tractor_payload = 100;

    /**
     * @ManyToOne(targetEntity="ShipRumpRole")
     * @JoinColumn(name="role_id", referencedColumnName="id")
     */
    private $shipRumpRole;

    /**
     * @ManyToOne(targetEntity="ShipRumpCategory")
     * @JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $shipRumpCategory;

    /**
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $commodity;

    /**
     * @ManyToOne(targetEntity="DatabaseEntry")
     * @JoinColumn(name="database_id", referencedColumnName="id")
     */
    private $databaseEntry;

    private $module_levels;

    private $crewobj;

    private $specialAbilities;

    /**
     * @OneToMany(targetEntity="ShipRumpCost", mappedBy="shipRump")
     */
    private $buildingCosts;

    public function __construct()
    {
        $this->buildingCosts = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setCategoryId(?int $categoryId): ShipRumpInterface
    {
        $this->category_id = $categoryId;

        return $this;
    }

    public function getCategoryId(): ?int
    {
        return $this->category_id;
    }

    public function getRoleId(): ?int
    {
        return $this->role_id;
    }

    public function setRoleId(?int $roleId): ShipRumpInterface
    {
        $this->role_id = $roleId;
        return $this;
    }

    public function getEvadeChance(): int
    {
        return $this->evade_chance;
    }

    public function setEvadeChance(int $evadeChance): ShipRumpInterface
    {
        $this->evade_chance = $evadeChance;
        return $this;
    }

    public function getHitChance(): int
    {
        return $this->hit_chance;
    }

    public function setHitChance(int $hitChance): ShipRumpInterface
    {
        $this->hit_chance = $hitChance;
        return $this;
    }

    public function getModuleLevel(): int
    {
        return $this->module_level;
    }

    public function setModuleLevel(int $moduleLevel): ShipRumpInterface
    {
        $this->module_level = $moduleLevel;
        return $this;
    }

    public function getBaseCrew(): int
    {
        return $this->base_crew;
    }

    public function setBaseCrew(int $baseCrew): ShipRumpInterface
    {
        $this->base_crew = $baseCrew;
        return $this;
    }

    public function getBaseEps(): int
    {
        return $this->base_eps;
    }

    public function setBaseEps(int $baseEps): ShipRumpInterface
    {
        $this->base_eps = $baseEps;
        return $this;
    }

    public function getBaseReactor(): int
    {
        return $this->base_reactor;
    }

    public function setBaseReactor(int $baseReactor): ShipRumpInterface
    {
        $this->base_reactor = $baseReactor;
        return $this;
    }

    public function getBaseHull(): int
    {
        return $this->base_hull;
    }

    public function setBaseHull(int $baseHull): ShipRumpInterface
    {
        $this->base_hull = $baseHull;
        return $this;
    }

    public function getBaseShield(): int
    {
        return $this->base_shield;
    }

    public function setBaseShield(int $baseShield): ShipRumpInterface
    {
        $this->base_shield = $baseShield;
        return $this;
    }

    public function getBaseDamage(): int
    {
        return $this->base_damage;
    }

    public function setBaseDamage(int $baseDamage): ShipRumpInterface
    {
        $this->base_damage = $baseDamage;
        return $this;
    }

    public function getBaseSensorRange(): int
    {
        return $this->base_sensor_range;
    }

    public function setBaseSensorRange(int $baseSensorRange): ShipRumpInterface
    {
        $this->base_sensor_range = $baseSensorRange;
        return $this;
    }

    public function getBaseTorpedoStorage(): int
    {
        return $this->base_torpedo_storage;
    }

    public function setBaseTorpedoStorage(int $baseTorpedoStorage): ShipRumpInterface
    {
        $this->base_torpedo_storage = $baseTorpedoStorage;
        return $this;
    }

    public function getBeamFactor(): int
    {
        return $this->beam_factor;
    }

    public function setBeamFactor(int $beamFactor): ShipRumpInterface
    {
        $this->beam_factor = $beamFactor;
        return $this;
    }

    public function getSpecialSlots(): int
    {
        return $this->special_slots;
    }

    public function setSpecialSlots(int $specialSlots): ShipRumpInterface
    {
        $this->special_slots = $specialSlots;
        return $this;
    }

    public function getShuttleSlots(): int
    {
        return $this->shuttle_slots;
    }

    public function setShuttleSlots(int $shuttleSlots): ShipRumpInterface
    {
        $this->shuttle_slots = $shuttleSlots;
        return $this;
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

    public function setPhaserVolleys(int $phaserVolleys): ShipRumpInterface
    {
        $this->phaser_volleys = $phaserVolleys;
        return $this;
    }

    public function getPhaserHullDamageFactor(): int
    {
        return $this->phaser_hull_damage_factor;
    }

    public function setPhaserHullDamageFactor(int $phaserHullDamageFactor): ShipRumpInterface
    {
        $this->phaser_hull_damage_factor = $phaserHullDamageFactor;
        return $this;
    }

    public function getPhaserShieldDamageFactor(): int
    {
        return $this->phaser_shield_damage_factor;
    }

    public function setPhaserShieldDamageFactor(int $phaserShieldDamageFactor): ShipRumpInterface
    {
        $this->phaser_shield_damage_factor = $phaserShieldDamageFactor;
        return $this;
    }

    public function getTorpedoLevel(): int
    {
        return $this->torpedo_level;
    }

    public function setTorpedoLevel(int $torpedoLevel): ShipRumpInterface
    {
        $this->torpedo_level = $torpedoLevel;
        return $this;
    }

    public function getTorpedoVolleys(): int
    {
        return $this->torpedo_volleys;
    }

    public function setTorpedoVolleys(int $torpedoVolleys): ShipRumpInterface
    {
        $this->torpedo_volleys = $torpedoVolleys;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ShipRumpInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getIsBuildable(): bool
    {
        return $this->is_buildable;
    }

    public function setIsBuildable(bool $isBuildable): ShipRumpInterface
    {
        $this->is_buildable = $isBuildable;
        return $this;
    }

    public function getIsNpc(): bool
    {
        return $this->is_npc;
    }

    public function setIsNpc(bool $isNpc): ShipRumpInterface
    {
        $this->is_npc = $isNpc;
        return $this;
    }

    public function getEpsCost(): int
    {
        return $this->eps_cost;
    }

    public function setEpsCost(int $energyCosts): ShipRumpInterface
    {
        $this->eps_cost = $energyCosts;
        return $this;
    }

    public function getStorage(): int
    {
        return $this->storage;
    }

    public function setStorage(int $storage): ShipRumpInterface
    {
        $this->storage = $storage;
        return $this;
    }

    public function getDockingSlots(): int
    {
        return $this->slots;
    }

    public function setDockingSlots(int $dockingSlots): ShipRumpInterface
    {
        $this->slots = $dockingSlots;
        return $this;
    }

    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    public function setBuildtime(int $buildtime): ShipRumpInterface
    {
        $this->buildtime = $buildtime;
        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort($sort): ShipRumpInterface
    {
        $this->sort = $sort;
        return $this;
    }

    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    public function setDatabaseId(?int $databaseId): ShipRumpInterface
    {
        $this->database_id = $databaseId;
        return $this;
    }

    public function getGoodId(): ?int
    {
        return $this->good_id;
    }

    public function setGoodId(?int $goodId): ShipRumpInterface
    {
        $this->good_id = $goodId;
        return $this;
    }

    public function getFlightEcost(): int
    {
        return $this->flight_ecost;
    }

    public function setFlightEcost(int $flightEcost): ShipRumpInterface
    {
        $this->flight_ecost = $flightEcost;
        return $this;
    }

    public function getShipRumpRole(): ?ShipRumpRoleInterface
    {
        return $this->shipRumpRole;
    }

    public function setShipRumpRole(?ShipRumpRoleInterface $shipRumpRole): ShipRumpInterface
    {
        $this->shipRumpRole = $shipRumpRole;
        return $this;
    }

    public function getShipRumpCategory(): ?ShipRumpCategoryInterface
    {
        return $this->shipRumpCategory;
    }

    public function setShipRumpCategory(?ShipRumpCategoryInterface $shipRumpCategory): ShipRumpInterface
    {
        $this->shipRumpCategory = $shipRumpCategory;
        return $this;
    }

    public function getCommodity(): ?CommodityInterface
    {
        return $this->commodity;
    }

    public function setCommodity(?CommodityInterface $commodity): ShipRumpInterface
    {
        $this->commodity = $commodity;
        return $this;
    }

    public function getNeededWorkbees(): ?int
    {
        return $this->needed_workbees;
    }

    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): ShipRumpInterface
    {
        $this->databaseEntry = $databaseEntry;
        return $this;
    }

    public function isTrumfield(): bool
    {
        return $this->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_DEBRISFIELD;
    }

    public function isEscapePods(): bool
    {
        return $this->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_ESCAPE_PODS;
    }

    public function isShipyard(): bool
    {
        return $this->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_STATION
            && $this->getRoleId() === ShipRumpEnum::SHIP_ROLE_SHIPYARD;
    }

    public function getModuleLevels(): ?ShipRumpModuleLevelInterface
    {
        if ($this->module_levels === null) {
            // @todo refactor
            global $container;

            $this->module_levels = $container->get(ShipRumpModuleLevelRepositoryInterface::class)->getByShipRump(
                (int) $this->getId()
            );
        }
        return $this->module_levels;
    }

    /**
     * @return ShipRumpCostInterface[]
     */
    public function getBuildingCosts(): Collection
    {
        return $this->buildingCosts;
    }

    public function hasSpecialAbility(int $value): bool
    {
        if ($this->specialAbilities === null) {
            // @todo refactor
            global $container;

            $this->specialAbilities = array_map(
                function (ShipRumpSpecialInterface $shipRumpSpecial): int {
                    return $shipRumpSpecial->getSpecialId();
                },
                $container->get(ShipRumpSpecialRepositoryInterface::class)->getByShipRump((int) $this->getId())
            );
        }
        return in_array($value, $this->specialAbilities);
    }

    private function getBaseCrewCount(): int
    {
        $count = $this->getBaseCrew();
        if ($this->getCrewObj() !== null) {
            foreach ([1, 2, 3, 4, 5, 7] as $slot) {
                $crew_func = 'getJob' . $slot . 'Crew';
                $count += $this->getCrewObj()->$crew_func();
            }
        }
        return $count;
    }

    public function getMaxCrewCount(): int
    {
        if ($this->getCrewObj() === null) {
            return $this->getBaseCrewCount();
        } else {
            return $this->getBaseCrewCount() + $this->getCrewObj()->getJob6Crew();
        }
    }

    public function getCrewObj(): ?ShipRumpCategoryRoleCrewInterface
    {
        if ($this->crewobj === null) {
            // @todo refactor
            global $container;

            $this->crewobj = $container->get(ShipRumpCategoryRoleCrewRepositoryInterface::class)
                ->getByShipRumpCategoryAndRole(
                    (int) $this->getCategoryId(),
                    (int) $this->getRoleId()
                );
        }
        return $this->crewobj;
    }

    public function getFractionId(): int
    {
        //last digit of id shows fraction id
        return $this->getId() % 10;
    }
}
