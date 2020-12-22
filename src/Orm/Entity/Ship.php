<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Module\Ship\Lib\PositionChecker;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Starmap\View\Overview\Overview;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRepository")
 * @Table(
 *     name="stu_ships",
 *     indexes={
 *         @Index(name="outer_system_location_idx", columns={"systems_id","cx","cy"}),
 *         @Index(name="inner_system_location_idx", columns={"systems_id","sx","sy"})
 *     }
 * )
 **/
class Ship implements ShipInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="integer") */
    private $rumps_id = 0;

    /** @Column(type="integer", nullable=true) */
    private $plans_id;

    /** @Column(type="integer", nullable=true) */
    private $fleets_id;

    /** @Column(type="integer", nullable=true) */
    private $systems_id;

    /** @Column(type="integer", length=5) */
    private $cx = 0;

    /** @Column(type="integer", length=5) */
    private $cy = 0;

    /** @Column(type="smallint", length=3) */
    private $sx = 0;

    /** @Column(type="smallint", length=3) */
    private $sy = 0;

    /** @Column(type="smallint", length=1) */
    private $direction = 0;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="smallint", length=1) */
    private $alvl = 0;

    /** @Column(type="boolean") */
    private $warp = false;

    /** @Column(type="integer", length=5) */
    private $warpcore = 0;

    /** @Column(type="boolean") */
    private $cloak = false;

    /** @Column(type="boolean") */
    private $cloakable = false;

    /** @Column(type="integer", length=6) */
    private $eps = 0;

    /** @Column(type="integer", length=6) */
    private $max_eps = 0;

    /** @Column(type="integer", length=6) */
    private $batt = 0;

    /** @Column(type="integer", length=6) */
    private $max_batt = 0;

    /** @Column(type="integer", length=6) */
    private $huelle = 0;

    /** @Column(type="integer", length=6) */
    private $max_huelle = 0;

    /** @Column(type="integer", length=6) */
    private $schilde = 0;

    /** @Column(type="integer", length=6) */
    private $max_schilde = 0;

    /** @Column(type="boolean") */
    private $schilde_status = false;

    /** @Column(type="integer") */
    private $traktor = 0;

    /** @Column(type="smallint", length=1) */
    private $traktormode = 0;

    /** @Column(type="integer", nullable=true) */
    private $dock;

    /** @Column(type="boolean") */
    private $nbs = false;

    /** @Column(type="boolean") */
    private $lss = false;

    /** @Column(type="boolean") */
    private $wea_phaser = false;

    /** @Column(type="boolean") */
    private $wea_torp = false;

    /** @Column(type="integer") */
    private $former_rumps_id = 0;

    /** @Column(type="integer", length=3, nullable=true) */
    private $torpedo_type;

    /** @Column(type="smallint", length=4) */
    private $torpedo_count = 0;

    /** @Column(type="smallint", length=4) */
    private $trade_post_id = 0;

    /** @Column(type="integer") */
    private $batt_wait = 0;

    /** @Column(type="boolean") */
    private $is_base = false;

    /** @Column(type="integer") */
    private $database_id = 0;

    /** @Column(type="boolean") */
    private $is_destroyed = false;

    /** @Column(type="boolean") */
    private $disabled = false;

    /** @Column(type="boolean") */
    private $can_be_disabled = false;

    /** @Column(type="smallint", length=3) */
    private $hit_chance = 0;

    /** @Column(type="smallint", length=3) */
    private $evade_chance = 0;

    /** @Column(type="smallint", length=4) */
    private $reactor_output = 0;

    /** @Column(type="smallint", length=4) */
    private $base_damage = 0;

    /** @Column(type="smallint", length=3) */
    private $sensor_range = 0;

    /** @Column(type="integer") */
    private $shield_regeneration_timer = 0;

    /** @Column(type="smallint", length=3) */
    private $state = 0;

    /**
     * @ManyToOne(targetEntity="Fleet", inversedBy="ships")
     * @JoinColumn(name="fleets_id", referencedColumnName="id")
     */
    private $fleet;

    /**
     * @OneToOne(targetEntity="TradePost", mappedBy="ship")
     */
    private $trade_post;

    /**
     * @ManyToOne(targetEntity="Ship", inversedBy="dockedShips")
     * @JoinColumn(name="dock", referencedColumnName="id")
     */
    private $dockedTo;

    /**
     * @OneToMany(targetEntity="Ship", mappedBy="dockedTo")
     */
    private $dockedShips;

    /**
     * @OneToMany(targetEntity="DockingPrivilege", mappedBy="ship")
     */
    private $dockingPrivileges;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @OneToMany(targetEntity="ShipCrew", mappedBy="ship")
     */
    private $crew;

    /**
     * @ManyToOne(targetEntity="StarSystem")
     * @JoinColumn(name="systems_id", referencedColumnName="id")
     */
    private $starSystem;

    /**
     * @ManyToOne(targetEntity="TorpedoType")
     * @JoinColumn(name="torpedo_type", referencedColumnName="id")
     */
    private $torpedo;

    /**
     * @OneToMany(targetEntity="ShipSystem", mappedBy="ship", indexBy="system_type")
     */
    private $systems;

    /**
     * @ManyToOne(targetEntity="ShipRump")
     * @JoinColumn(name="rumps_id", referencedColumnName="id")
     */
    private $rump;

    /**
     * @ManyToOne(targetEntity="ShipBuildplan")
     * @JoinColumn(name="plans_id", referencedColumnName="id")
     */
    private $buildplan;

    /**
     * @OneToMany(targetEntity="ShipStorage", mappedBy="ship", indexBy="goods_id")
     */
    private $storage;

    private $epsUsage;

    private $mapfield;

    private $effectiveEpsProduction;

    private $isOverStarSystem;

    public function __construct()
    {
        $this->dockedShips = new ArrayCollection();
        $this->dockingPrivileges = new ArrayCollection();
        $this->crew = new ArrayCollection();
        $this->systems = new ArrayCollection();
        $this->storage = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getFleetId(): ?int
    {
        return $this->fleets_id;
    }

    public function setFleetId(?int $fleetId): ShipInterface
    {
        $this->fleets_id = $fleetId;
        return $this;
    }

    public function getSystemsId(): ?int
    {
        return $this->systems_id;
    }

    public function getCx(): int
    {
        return $this->cx;
    }

    public function setCx(int $cx): ShipInterface
    {
        $this->cx = $cx;
        return $this;
    }

    public function getCy(): int
    {
        return $this->cy;
    }

    public function setCy(int $cy): ShipInterface
    {
        $this->cy = $cy;
        return $this;
    }

    public function getSx(): int
    {
        return $this->sx;
    }

    public function setSx(int $sx): ShipInterface
    {
        $this->sx = $sx;
        return $this;
    }

    public function getSy(): int
    {
        return $this->sy;
    }

    public function setSy(int $sy): ShipInterface
    {
        $this->sy = $sy;
        return $this;
    }

    public function getFlightDirection(): int
    {
        return $this->direction;
    }

    public function setFlightDirection(int $direction): ShipInterface
    {
        $this->direction = $direction;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ShipInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getAlertState(): int
    {
        return $this->alvl;
    }

    public function setAlertState(int $alertState): ShipInterface
    {
        //check if enough energy
        if ($alertState == ShipAlertStateEnum::ALERT_YELLOW
            && $this->alvl == ShipAlertStateEnum::ALERT_GREEN)
        {
            if ($this->getEps() < 1) {
                throw new InsufficientEnergyException();
            }
            $this->eps -= 1;
        }
        if ($alertState == ShipAlertStateEnum::ALERT_RED
            && $this->alvl !== ShipAlertStateEnum::ALERT_RED)
        {
            if ($this->getEps() < 2) {
                throw new InsufficientEnergyException();
            }
            $this->eps -= 2;
        }

        // now change
        $this->alvl = $alertState;
        $this->reloadEpsUsage();

        return $this;
    }

    private function getSystemState(int $systemId): bool
    {
        return $this->getShipSystem($systemId)->getMode() === ShipSystemModeEnum::MODE_ON
            || $this->getShipSystem($systemId)->getMode() === ShipSystemModeEnum::MODE_ALWAYS_ON;
    }

    private function setSystemMode(int $systemId, int $mode): void
    {
        $this->getShipSystem($systemId)->setMode($mode);
    }

    public function getWarpState(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
    }

    public function getWarpcoreLoad(): int
    {
        return $this->warpcore;
    }

    public function setWarpcoreLoad(int $warpcoreLoad): ShipInterface
    {
        $this->warpcore = $warpcoreLoad;
        return $this;
    }

    public function getCloakState(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_CLOAK);
    }

    public function getEps(): int
    {
        return $this->eps;
    }

    public function setEps(int $eps): ShipInterface
    {
        $this->eps = $eps;
        return $this;
    }

    public function getMaxEps(): int
    {
        return $this->max_eps;
    }

    public function setMaxEps(int $maxEps): ShipInterface
    {
        $this->max_eps = $maxEps;
        return $this;
    }

    public function getEBatt(): int
    {
        return $this->batt;
    }

    public function setEBatt(int $batt): ShipInterface
    {
        $this->batt = $batt;
        return $this;
    }

    public function getMaxEBatt(): int
    {
        return $this->max_batt;
    }

    public function setMaxEBatt(int $maxBatt): ShipInterface
    {
        $this->max_batt = $maxBatt;
        return $this;
    }

    public function getHuell(): int
    {
        return $this->huelle;
    }

    public function setHuell(int $hull): ShipInterface
    {
        $this->huelle = $hull;
        return $this;
    }

    public function getMaxHuell(): int
    {
        return $this->max_huelle;
    }

    public function setMaxHuell(int $maxHull): ShipInterface
    {
        $this->max_huelle = $maxHull;
        return $this;
    }

    public function getShield(): int
    {
        return $this->schilde;
    }

    public function setShield(int $schilde): ShipInterface
    {
        $this->schilde = $schilde;
        return $this;
    }

    public function getMaxShield(): int
    {
        return $this->max_schilde;
    }

    public function setMaxShield(int $maxShields): ShipInterface
    {
        $this->max_schilde = $maxShields;
        return $this;
    }

    public function getShieldState(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_SHIELDS);
    }

    public function getTraktorShipId(): int
    {
        return $this->traktor;
    }

    public function setTraktorShipId(int $traktorShipId): ShipInterface
    {
        $this->traktor = $traktorShipId;
        return $this;
    }

    public function getTraktormode(): int
    {
        return $this->traktormode;
    }

    public function setTraktormode(int $traktormode): ShipInterface
    {
        $this->traktormode = $traktormode;
        return $this;
    }

    public function getNbs(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_NBS);
    }
    
    public function getLss(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_LSS);
    }

    public function canActivatePhaser(): bool
    {
        //TODO rework über CloakShipSystem implements ShipSystemTypeInterface?
        return $this->getAlertState() !== ShipAlertStateEnum::ALERT_GREEN;
    }

    public function getPhaser(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_PHASER);
    }
    
    public function canActivateTorpedos(): bool
    {
        //TODO rework über CloakShipSystem implements ShipSystemTypeInterface?
        return $this->getAlertState() !== ShipAlertStateEnum::ALERT_GREEN;
    }
    
    public function getTorpedos(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_TORPEDO);
    }

    public function getFormerRumpId(): int
    {
        return $this->former_rumps_id;
    }

    public function setFormerRumpId(int $formerShipRumpId): ShipInterface
    {
        $this->former_rumps_id = $formerShipRumpId;
        return $this;
    }

    public function getTorpedoCount(): int
    {
        return $this->torpedo_count;
    }

    public function setTorpedoCount(int $torpedoAmount): ShipInterface
    {
        $this->torpedo_count = $torpedoAmount;
        return $this;
    }

    public function getTradePostId(): int
    {
        return $this->trade_post_id;
    }

    public function setTradePostId(int $tradePostId): ShipInterface
    {
        $this->trade_post_id = $tradePostId;
        return $this;
    }

    public function getEBattWaitingTime(): int
    {
        return $this->batt_wait;
    }

    public function setEBattWaitingTime(int $batteryCooldown): ShipInterface
    {
        $this->batt_wait = $batteryCooldown;
        return $this;
    }

    public function isBase(): bool
    {
        return $this->is_base;
    }

    public function setIsBase(bool $isBase): ShipInterface
    {
        $this->is_base = $isBase;
        return $this;
    }

    public function getDatabaseId(): int
    {
        return $this->database_id;
    }

    public function setDatabaseId(int $databaseEntryId): ShipInterface
    {
        $this->database_id = $databaseEntryId;
        return $this;
    }

    public function getIsDestroyed(): bool
    {
        return $this->is_destroyed;
    }

    public function setIsDestroyed(bool $isDestroyed): ShipInterface
    {
        $this->is_destroyed = $isDestroyed;
        return $this;
    }

    public function getDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): ShipInterface
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function getCanBeDisabled(): bool
    {
        return $this->can_be_disabled;
    }

    public function setCanBeDisabled(bool $canBeDisabled): ShipInterface
    {
        $this->can_be_disabled = $canBeDisabled;
        return $this;
    }

    public function getHitChance(): int
    {
        return $this->hit_chance;
    }

    public function setHitChance(int $hitChance): ShipInterface
    {
        $this->hit_chance = $hitChance;
        return $this;
    }

    public function getEvadeChance(): int
    {
        return $this->evade_chance;
    }

    public function setEvadeChance(int $evadeChance): ShipInterface
    {
        $this->evade_chance = $evadeChance;
        return $this;
    }

    public function getReactorOutput(): int
    {
        return $this->reactor_output;
    }

    public function setReactorOutput(int $reactorOutput): ShipInterface
    {
        $this->reactor_output = $reactorOutput;
        return $this;
    }

    public function getBaseDamage(): int
    {
        return $this->base_damage;
    }

    public function setBaseDamage(int $baseDamage): ShipInterface
    {
        $this->base_damage = $baseDamage;
        return $this;
    }

    public function getSensorRange(): int
    {
        return $this->sensor_range;
    }

    public function setSensorRange(int $sensorRange): ShipInterface
    {
        $this->sensor_range = $sensorRange;
        return $this;
    }

    public function getShieldRegenerationTimer(): int
    {
        return $this->shield_regeneration_timer;
    }

    public function setShieldRegenerationTimer(int $shieldRegenerationTimer): ShipInterface
    {
        $this->shield_regeneration_timer = $shieldRegenerationTimer;
        return $this;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): ShipInterface
    {
        $this->state = $state;
        return $this;
    }

    public function getCrewlist(): Collection
    {
        return $this->crew;
    }

    public function getPosX(): int
    {
        if ($this->getSystem() !== null) {
            return $this->getSX();
        }
        return $this->getCX();
    }

    public function getPosY(): int
    {
        if ($this->getSystem() !== null) {
            return $this->getSY();
        }
        return $this->getCY();
    }

    public function getCrewCount(): int
    {
        return $this->getCrewlist()->count();
    }

    public function leaveFleet(): void
    {
        $fleet = $this->getFleet();

        if ($fleet !== null) {
            $fleet->getShips()->removeElement($this);

            $this->setFleet(null);
            $this->setFleetId(null);

            // @todo refactor
            global $container;

            $container->get(ShipRepositoryInterface::class)->save($this);
        }
    }

    public function getFleet(): ?FleetInterface
    {
        return $this->fleet;
    }

    public function setFleet(?FleetInterface $fleet): ShipInterface
    {
        $this->fleet = $fleet;
        return $this;
    }

    public function isFleetLeader(): bool
    {
        if ($this->getFleet() === null) {
            return false;
        }
        return $this->getFleet()->getLeadShip()->getId() === $this->getId();
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): ShipInterface
    {
        $this->user = $user;
        return $this;
    }

    public function setPosX(int $value): void
    {
        if ($this->getSystem() !== null) {
            $this->setSX($value);
            return;
        }
        $this->setCX($value);
    }

    public function setPosY($value): void
    {
        if ($this->getSystem() !== null) {
            $this->setSY($value);
            return;
        }
        $this->setCY($value);
    }

    public function getSystem(): ?StarSystemInterface
    {
        return $this->starSystem;
    }

    public function setSystem(?StarSystemInterface $starSystem): ShipInterface
    {
        $this->starSystem = $starSystem;
        return $this;
    }

    public function getWarpcoreCapacity(): int
    {
        return $this->getReactorOutput() * ShipEnum::WARPCORE_CAPACITY_MULTIPLIER;
    }

    public function getReactorCapacity(): int
    {
        if ($this->getReactorOutput() > $this->getWarpcoreLoad()) {
            return $this->getWarpcoreLoad();
        }
        return $this->getReactorOutput();
    }

    public function getEffectiveEpsProduction(): int
    {
        if ($this->effectiveEpsProduction === null) {
            $prod = $this->getReactorCapacity() - $this->getEpsUsage();
            if ($prod <= 0) {
                return $prod;
            }
            if ($this->getEps() + $prod > $this->getMaxEps()) {
                return $this->getMaxEps() - $this->getEps();
            }
            $this->effectiveEpsProduction = $prod;
        }
        return $this->effectiveEpsProduction;
    }

    public function getWarpcoreUsage(): int
    {
        return $this->getEffectiveEpsProduction() + $this->getEpsUsage();
    }

    public function isEBattUseable(): bool
    {
        return $this->getEBattWaitingTime() < time();
    }

    public function isWarpAble(): bool
    {
        // @todo TBD damaged warp coils
        return true;
    }

    public function isTraktorbeamActive(): bool
    {
        return $this->getTraktorMode() > 0;
    }

    public function traktorBeamFromShip(): bool
    {
        return $this->getTraktorMode() == 1;
    }

    public function traktorBeamToShip(): bool
    {
        return $this->getTraktorMode() == 2;
    }

    public function getTraktorShip(): ?ShipInterface
    {
        // @todo refactor
        global $container;

        return $container->get(ShipRepositoryInterface::class)->find($this->getTraktorShipId());
    }

    public function unsetTraktor(): void
    {
        $this->setTraktorMode(0);
        $this->setTraktorShipId(0);

        // @todo refactor
        global $container;

        $container->get(ShipRepositoryInterface::class)->save($this);
    }

    public function deactivateTraktorBeam(): void
    {
        if (!$this->getTraktorMode()) {
            return;
        }
        $ship = $this->getTraktorShip();
        $this->setTraktorMode(0);
        $this->setTraktorShipId(0);
        $ship->setTraktorMode(0);
        $ship->setTraktorShipId(0);
        // @todo refactor
        global $container;

        $shipRepo = $container->get(ShipRepositoryInterface::class);

        $shipRepo->save($this);
        $shipRepo->save($ship);
    }

    public function isOverSystem(): ?StarSystemInterface
    {
        if ($this->getSystem() !== null) {
            return null;
        }
        if ($this->isOverStarSystem === null) {
            // @todo refactor
            global $container;

            $this->isOverStarSystem = $container->get(StarSystemRepositoryInterface::class)->getByCoordinates(
                (int)$this->getCX(),
                (int)$this->getCY()
            );
        }
        return $this->isOverStarSystem;
    }

    public function isWarpPossible(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE) && $this->getSystem() === null;
    }

    public function getTorpedo(): ?TorpedoTypeInterface
    {
        return $this->torpedo;
    }

    public function setTorpedo(?TorpedoTypeInterface $torpedoType): ShipInterface
    {
        $this->torpedo = $torpedoType;
        return $this;
    }

    public function getSortedStorage(): array
    {
        $array = $this->getStorage()->getValues();

        usort(
            $array,
            function (ShipStorageInterface $a, ShipStorageInterface $b): int {
                if ($a->getCommodity()->getSort() == $b->getCommodity()->getSort()) {
                    return 0;
                }
                return ($a->getCommodity()->getSort() < $b->getCommodity()->getSort()) ? -1 : 1;
            }
        );

        return array_values($array);
    }

    public function getStorage(): Collection
    {
        return $this->storage;
    }

    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage()->getValues(),
            function (int $sum, ShipStorageInterface $storage): int {
                return $sum + $storage->getAmount();
            },
            0
        );
    }

    public function getMaxStorage(): int
    {
        return $this->getRump()->getStorage();
    }
    
    public function getBeamFactor(): int
    {
        return $this->getRump()->getBeamFactor();
    }

    public function getSectorString(): string
    {
        $str = $this->getPosX() . '|' . $this->getPosY();
        if ($this->getSystem() !== null) {
            $str .= ' (' . $this->getSystem()->getName() . '-System)';
        }
        return $str;
    }

    public function getBuildplan(): ?ShipBuildplanInterface
    {
        return $this->buildplan;
    }

    public function setBuildplan(?ShipBuildplanInterface $shipBuildplan): ShipInterface
    {
        $this->buildplan = $shipBuildplan;
        return $this;
    }

    public function getEpsUsage(): int
    {
        if ($this->epsUsage === null) {
            $this->reloadEpsUsage();
        }
        return $this->epsUsage;
    }

    private function reloadEpsUsage(): void
    {
        $this->epsUsage = array_reduce(
            $this->getActiveSystems(),
            function (int $sum, ShipSystemInterface $shipSystem): int {
                return $sum + $shipSystem->getEnergyCosts();
            },
            0
        );
        if ($this->getAlertState() == ShipAlertStateEnum::ALERT_YELLOW)
        {
            $this->epsUsage += 1;
        }
        if ($this->getAlertState() == ShipAlertStateEnum::ALERT_RED)
        {
            $this->epsUsage += 2;
        }
    }

    public function lowerEpsUsage($value): void
    {
        $this->epsUsage -= $value;
    }

    public function getSystems(): Collection
    {
        return $this->systems;
    }

    // with ShipSystemTypeEnum
    public function hasShipSystem($system): bool
    {
        return $this->getSystems()->containsKey($system);
    }

    // with ShipSystemTypeEnum
    public function getShipSystem($system): ShipSystemInterface
    {
        return $this->getSystems()->get($system);
    }

    /**
     * @return ShipSystemInterface[]
     */
    public function getActiveSystems(): array
    {
        $activeSystems = [];
        foreach ($this->getSystems() as $system) {
            if ($system->getMode() > 1) {
                $this->activeSystems[] = $system;
            }
        }
        return $activeSystems;
    }

    public function displayNbsActions(): bool
    {
        return $this->getCloakState() == 0 && $this->getWarpstate() == 0;
    }

    public function traktorbeamNotPossible(): bool
    {
        return $this->isBase() || $this->getRump()->isTrumfield() || $this->getCloakState() || $this->getShieldState() || $this->getWarpState();
    }

    public function isInterceptAble(): bool
    {
        return $this->getWarpState();
    }

    public function getMapCX(): int
    {
        return (int) ceil($this->getCX() / Overview::FIELDS_PER_SECTION);
    }

    public function getMapCY(): int
    {
        return (int)ceil($this->getCY() / Overview::FIELDS_PER_SECTION);
    }

    public function dockedOnTradePost(): bool
    {
        return $this->getDockedTo() && $this->getDockedTo()->getTradePostId() > 0;
    }

    public function getDockPrivileges(): Collection
    {
        return $this->dockingPrivileges;
    }

    public function hasFreeDockingSlots(): bool
    {
        return $this->getRump()->getDockingSlots() > $this->getDockedShipCount();
    }

    public function getFreeDockingSlotCount(): int
    {
        return $this->getRump()->getDockingSlots() - $this->getDockedShipCount();
    }

    public function getDockedShipCount(): int
    {
        return $this->dockedShips->count();
    }

    // @todo interface
    public function getCurrentMapField()
    {
        if ($this->mapfield === null) {
            // @todo refactor
            global $container;
            if ($this->getSystem() === null) {
                $this->mapfield = $container->get(MapRepositoryInterface::class)->getByCoordinates(
                    $this->getCX(),
                    $this->getCY()
                );
            } else {
                // @todo refactor
                global $container;

                $this->mapfield = $container->get(StarSystemMapRepositoryInterface::class)->getByCoordinates(
                    $this->getSystem()->getId(),
                    $this->getSX(),
                    $this->getSY()
                );
            }
        }
        return $this->mapfield;
    }

    private function getShieldRegenerationPercentage(): int
    {
        // @todo
        return 10;
    }

    public function getShieldRegenerationRate(): int
    {
        return (int)ceil(($this->getMaxShield() / 100) * $this->getShieldRegenerationPercentage());
    }

    public function canIntercept(): bool
    {
        return !$this->getTraktorMode();
    }
	
	public function isOwnedByCurrentUser(): bool
    {
        global $container;
        if ($container->get(GameControllerInterface::class)->getUser() !== $this->getUser()) {
	        return false;
        }
        return true;
    }

    public function canLandOnCurrentColony(): bool
    {
        if (!$this->getRump()->getGoodId()) {
            return false;
        }
        // @todo refactor
        global $container;

        $colonyRepository = $container->get(ColonyRepositoryInterface::class);

        $currentColony = $colonyRepository->getByPosition(
            $this->getSystem(),
            $this->getPosX(),
            $this->getPosY()
        );

        if ($currentColony === null) {
            return false;
        }
        if ($currentColony->getUser() !== $this->getUser()) {
            return false;
        }

        return $container->get(ColonyLibFactoryInterface::class)
            ->createColonySurface($currentColony)
            ->hasAirfield();
    }

    public function canBeAttacked(): bool
    {
        return !$this->getRump()->isTrumfield();
    }

    public function canAttack(): bool
    {
        return $this->getPhaser() || $this->getTorpedos();
    }

    public function hasEscapePods(): bool
    {
        return $this->getRump()->isTrumfield() && $this->getCrewCount() > 0;
    }

    public function canBeRepaired(): bool
    {
        // @todo
        if ($this->getHuell() >= $this->getMaxHuell()) {
            return false;
        }
        if ($this->getShieldState()) {
            return false;
        }
        return true;
    }

    public function cancelRepair(): void
    {
        if ($this->getState() == ShipStateEnum::SHIP_STATE_REPAIR) {
            $this->setState(ShipStateEnum::SHIP_STATE_NONE);

            // @todo inject
            global $container;
            $container->get(ColonyShipRepairRepositoryInterface::class)->truncateByShipId($this->getId());
            $container->get(ShipRepositoryInterface::class)->save($this);
        }
    }

    public function getRepairRate(): int
    {
        // @todo
        return 100;
    }

    public function canInteractWith($target, bool $colony = false): bool
    {
        if ($target->getUser()->isVacationRequestOldEnough()) {
            global $container;
            $game = $container->get(GameControllerInterface::class);
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            
            return false;
        }

        $positionChecker = new PositionChecker();
        if ($this->getCloakState()) {
            return false;
        }
        if ($colony === true) {
            if (!$positionChecker->checkColonyPosition($target, $this) || $target->getId() == $this->getId()) {
                return false;
            }
            return true;

        } else {
            if (!$positionChecker->checkPosition($this, $target)) {
                return false;
            }
        }
        if ($target->getShieldState() && $target->getUserId() != $this->getUser()->getId()) {
            return false;
        }
        return true;
    }

    public function hasActiveWeapons(): bool
    {
        return $this->getPhaser() || $this->getTorpedos();
    }

    public function getRump(): ShipRumpInterface
    {
        return $this->rump;
    }

    public function setRump(ShipRumpInterface $shipRump): ShipInterface
    {
        $this->rump = $shipRump;
        return $this;
    }

    public function hasPhaser(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER);
    }

    public function hasTorpedo(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO);
    }

    public function hasCloak(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_CLOAK);
    }

    public function hasWarpcore(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPCORE);
    }

    public function getMaxTorpedos(): int
    {
        return $this->getRump()->getBaseTorpedoStorage();
    }

    public function getDockedShips(): Collection
    {
        return $this->dockedShips;
    }

    public function getDockedTo(): ?ShipInterface
    {
        return $this->dockedTo;
    }

    public function setDockedTo(?ShipInterface $dockedTo): ShipInterface
    {
        $this->dockedTo = $dockedTo;
        return $this;
    }

}
