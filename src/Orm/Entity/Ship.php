<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use AccessViolation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Starmap\View\Overview\Overview;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRepository")
 * @Table(
 *     name="stu_ships",
 *     indexes={
 *     }
 * )
 **/
class Ship implements ShipInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="integer") */
    private $rumps_id = 0;

    /** @Column(type="integer") */
    private $plans_id = 0;

    /** @Column(type="integer", nullable=true) */
    private $fleets_id;

    /** @Column(type="integer") */
    private $systems_id = 0;

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

    /** @Column(type="smallint", length=3) */
    private $torpedo_type = 0;

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
     * @ManyToOne(targetEntity="Ship", inversedBy="dockerShips")
     * @JoinColumn(name="dock", referencedColumnName="id")
     */
    private $dockedTo;

    /**
     * @OneToMany(targetEntity="Ship", mappedBy="dockedTo")
     */
    private $dockedShips;

    private $torpedo;

    private $activeSystems;

    private $epsUsage;

    private $systems;

    private $dockPrivileges;

    private $mapfield;

    private $rump;

    private $buildplan;

    private $currentColony;

    private $storage;

    private $traktorship;

    private $effectiveEpsProduction;

    private $system;

    private $crew;

    public function __construct()
    {
        $this->dockedShips = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): ShipInterface
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getRumpId(): int
    {
        return $this->rumps_id;
    }

    public function setRumpId(int $shipRumpId): ShipInterface
    {
        $this->rumps_id = $shipRumpId;
        return $this;
    }

    public function getBuildplanId(): int
    {
        return $this->plans_id;
    }

    public function setBuildplanId(int $buildPlanId): ShipInterface
    {
        $this->plans_id = $buildPlanId;
        return $this;
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

    public function getSystemsId(): int
    {
        return $this->systems_id;
    }

    public function setSystemsId(int $starSystemId): ShipInterface
    {
        $this->systems_id = $starSystemId;
        return $this;
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
        $this->alvl = $alertState;
        return $this;
    }

    public function getWarpState(): bool
    {
        return $this->warp;
    }

    public function setWarpState(bool $warpState): ShipInterface
    {
        $this->warp = $warpState;
        return $this;
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
        return $this->cloak;
    }

    public function setCloakState(bool $cloakState): ShipInterface
    {
        $this->cloak = $cloakState;
        return $this;
    }

    public function isCloakable(): bool
    {
        return $this->cloakable;
    }

    public function setCloakable(bool $cloakable): ShipInterface
    {
        $this->cloakable = $cloakable;
        return $this;
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
        return $this->schilde_status;
    }

    public function setShieldState(bool $shieldState): ShipInterface
    {
        $this->schilde_status = $shieldState;
        return $this;
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
        return $this->nbs;
    }

    public function setNbs(bool $nbs): ShipInterface
    {
        $this->nbs = $nbs;
        return $this;
    }

    public function getLss(): bool
    {
        return $this->lss;
    }

    public function setLss(bool $lss): ShipInterface
    {
        $this->lss = $lss;
        return $this;
    }

    public function getPhaser(): bool
    {
        return $this->wea_phaser;
    }

    public function setPhaser(bool $energyWeaponState): ShipInterface
    {
        $this->wea_phaser = $energyWeaponState;
        return $this;
    }

    public function getTorpedos(): bool
    {
        return $this->wea_torp;
    }

    public function setTorpedos(bool $projectileWeaponState): ShipInterface
    {
        $this->wea_torp = $projectileWeaponState;
        return $this;
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

    public function getTorpedoType(): int
    {
        return $this->torpedo_type;
    }

    public function setTorpedoType(int $torpedoTypeId): ShipInterface
    {
        $this->torpedo_type = $torpedoTypeId;
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

    public function getCrewlist(): array
    {
        if ($this->crew === null) {
            // @todo refactor
            global $container;

            $this->crew = $container->get(ShipCrewRepositoryInterface::class)->getByShip((int)$this->getId());
        }
        return $this->crew;
    }

    public function getPosX(): int
    {
        if ($this->getSystemsId() > 0) {
            return $this->getSX();
        }
        return $this->getCX();
    }

    public function getPosY(): int
    {
        if ($this->getSystemsId() > 0) {
            return $this->getSY();
        }
        return $this->getCY();
    }

    public function getCrewCount(): int
    {
        // @todo refactor
        global $container;

        return $container->get(ShipCrewRepositoryInterface::class)->getAmountByShip((int)$this->getId());
    }

    public function leaveFleet(): void
    {
        $this->setFleetId(0);

        // @todo refactor
        global $container;

        $container->get(ShipRepositoryInterface::class)->save($this);
    }

    public function ownedByCurrentUser(): bool
    {
        return $this->getUserId() == currentUser()->getId();
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
        if ($this->fleet === null) {
            return false;
        }
        return $this->getFleet()->getLeadShip()->getId() === $this->getId();
    }

    public function getUser(): UserInterface
    {
        // @todo refactor
        global $container;

        return $container->get(UserRepositoryInterface::class)->find($this->getUserId());
    }

    public function setPosX(int $value): void
    {
        if ($this->getSystemsId() > 0) {
            $this->setSX($value);
            return;
        }
        $this->setCX($value);
    }

    public function setPosY($value): void
    {
        if ($this->getSystemsId() > 0) {
            $this->setSY($value);
            return;
        }
        $this->setCY($value);
    }

    public function getSystem(): ?StarSystemInterface
    {
        if ($this->system === null) {
            // @todo refactor
            global $container;

            $this->system = $container->get(StarSystemRepositoryInterface::class)->find((int)$this->getSystemsId());
        }
        return $this->system;
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

        return $container->get(ShipRepositoryInterface::class)->save($this->getTraktorShip());
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
    // @todo interface
    public function isOverSystem()
    {
        if ($this->getSystemsId() > 0) {
            return false;
        }
        if ($this->system === null) {
            // @todo refactor
            global $container;

            $this->system = $container->get(StarSystemRepositoryInterface::class)->getByCoordinates(
                (int)$this->getCX(),
                (int)$this->getCY()
            );
        }
        return $this->system;
    }

    public function isWarpPossible(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE) && $this->getSystemsId() == 0;
    }

    public function getTorpedo(): ?TorpedoTypeInterface
    {
        if ($this->torpedo === null) {
            // @todo refactor
            global $container;

            $this->torpedo = $container->get(TorpedoTypeRepositoryInterface::class)->find((int)$this->getTorpedoType());
        }
        return $this->torpedo;
    }

    public function damage(DamageWrapper $damage_wrapper): array
    {
        $this->setShieldRegenerationTimer(time());
        $msg = [];
        if ($this->getShieldState()) {
            $damage = $damage_wrapper->getDamageRelative($this, ShipEnum::DAMAGE_MODE_SHIELDS);
            if ($damage > $this->getShield()) {
                $msg[] = "- Schildschaden: " . $this->getShield();
                $msg[] = "-- Schilde brechen zusammen!";
                $this->setShieldState(false);
                $this->setShield(0);
            } else {
                $this->setShield($this->getShield() - $damage);
                $msg[] = "- Schildschaden: " . $damage . " - Status: " . $this->getShield();
            }
        }
        if ($damage_wrapper->getDamage() <= 0) {
            return $msg;
        }
        $disablemessage = false;
        $damage = $damage_wrapper->getDamageRelative($this, ShipEnum::DAMAGE_MODE_HULL);
        if ($this->getCanBeDisabled() && $this->getHuell() - $damage < round($this->getMaxHuell() / 100 * 10)) {
            $damage = round($this->getHuell() - $this->getMaxHuell() / 100 * 10);
            $disablemessage = _('-- Das Schiff wurde kampfunfähig gemacht');
            $this->setDisabled(true);
        }
        if ($this->getHuell() > $damage) {
            $this->setHuell($this->getHuell() - $damage);
            $msg[] = "- Hüllenschaden: " . $damage . " - Status: " . $this->getHuell();
            if ($disablemessage) {
                $msg[] = $disablemessage;
            }
            return $msg;
        }
        $msg[] = "- Hüllenschaden: " . $damage;
        $msg[] = "-- Das Schiff wurde zerstört!";
        $this->setIsDestroyed(true);
        return $msg;
    }

    /**
     * @return ShipStorageInterface[] Indexed by commodityId
     */
    public function getStorage(): array
    {
        if ($this->storage === null) {
            // @todo refactor
            global $container;

            $this->storage = $container->get(ShipStorageRepositoryInterface::class)->getByShip((int)$this->getId());
        }
        return $this->storage;
    }

    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage(),
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

    public function getCurrentColony(): ?ColonyInterface
    {
        if ($this->currentColony === null) {
            // @todo refactor
            global $container;

            $colonyRepository = $container->get(ColonyRepositoryInterface::class);

            $this->currentColony = $colonyRepository->getByPosition(
                (int)$this->getSystemsId(),
                (int)$this->getPosX(),
                (int)$this->getPosY()
            );
        }
        return $this->currentColony;
    }

    public function getSectorString(): string
    {
        $str = $this->getPosX() . '|' . $this->getPosY();
        if ($this->getSystemsId() > 0) {
            $str .= ' (' . $this->getSystem()->getName() . '-System)';
        }
        return $str;
    }

    public function getBuildplan(): ShipBuildplanInterface
    {
        if ($this->buildplan === null) {
            // @todo refactor
            global $container;

            $this->buildplan = $container->get(ShipBuildplanRepositoryInterface::class)->find(
                (int)$this->getBuildplanId()
            );
        }
        return $this->buildplan;
    }

    public function getEpsUsage(): int
    {
        if ($this->epsUsage === null) {
            $this->epsUsage = 0;
            foreach ($this->getActiveSystems() as $key => $obj) {
                $this->epsUsage += $obj->getEnergyCosts();
            }
        }
        return $this->epsUsage;
    }

    public function lowerEpsUsage($value): int
    {
        $this->epsUsage -= $value;
    }

    public function getSystems(): array
    {
        if ($this->systems === null) {
            // @todo refactor
            global $container;

            $this->systems = [];
            foreach ($container->get(ShipSystemRepositoryInterface::class)->getByShip((int)$this->getId()) as $system) {
                $this->systems[$system->getSystemType()] = $system;
            }
        }
        return $this->systems;
    }

    public function hasShipSystem($system): bool
    {
        return array_key_exists($system, $this->getSystems());
    }

    public function getShipSystem($system): ShipSystemInterface
    {
        $arr = &$this->getSystems();
        return $arr[$system];
    }

    /**
     * @return ShipSystemInterface[]
     */
    public function getActiveSystems(): array
    {
        if ($this->activeSystems !== null) {
            return $this->activeSystems;
        }
        $ret = [];
        foreach ($this->getSystems() as $key => $obj) {
            if (!$this->isActiveSystem($obj)) {
                continue;
            }
            $ret[$key] = $obj;
        }
        return $this->activeSystems = $ret;
    }

    // @todo fix
    public function isActiveSystem($system): bool
    {
        return $this->data[$this->getShipField($system)] >= 1;
    }

    private function getShipField(ShipSystemInterface $shipSystem): string
    {
        switch ($shipSystem->getSystemType()) {
            case ShipSystemTypeEnum::SYSTEM_CLOAK:
                return 'cloak';
            case ShipSystemTypeEnum::SYSTEM_NBS:
                return 'nbs';
            case ShipSystemTypeEnum::SYSTEM_LSS:
                return 'lss';
            case ShipSystemTypeEnum::SYSTEM_PHASER:
                return 'wea_phaser';
            case ShipSystemTypeEnum::SYSTEM_TORPEDO:
                return 'wea_torp';
            case ShipSystemTypeEnum::SYSTEM_WARPDRIVE:
                return 'warp';
            case ShipSystemTypeEnum::SYSTEM_SHIELDS:
                return 'schilde_status';
        }
        return '';
    }

    public function systemIsActivateable(int $system): bool
    {
        if (!$this->hasShipSystem($system)) {
            return false;
        }
        if (!$this->getShipSystem($system)->isActivateable()) {
            return false;
        }
        if ($this->getShipSystem($system)->getEnergyCosts() > $this->getEps()) {
            return false;
        }
        if (array_key_exists($system, $this->getActiveSystems())) {
            return false;
        }
        switch ($system) {
            case ShipSystemTypeEnum::SYSTEM_SHIELDS:
                if ($this->getShield() == 0) {
                    return false;
                }
        }
        return true;
    }

    public function activateSystem(int $system, bool $use_eps = true): void
    {
        if (!$this->hasShipSystem($system)) {
            return;
        }
        $cb = $this->getShipSystem($system)->getShipCallback();
        $this->$cb(1);
        if ($use_eps) {
            $this->setEps($this->getEps() - $this->getShipSystem($system)->getEnergyCosts());
        }
    }

    public function deactivateSystem(int $system): void
    {
        if (!$this->hasShipSystem($system)) {
            return;
        }
        $cb = $this->getShipSystem($system)->getShipCallback();
        $this->$cb(0);
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
        return $this->getUserId() != currentUser()->getId() && $this->getWarpState();
    }

    public function getMapCX(): int
    {
        return (int) ceil($this->getCX() / Overview::FIELDS_PER_SECTION);
    }

    public function getMapCY(): int
    {
        return (int)ceil($this->getCY() / Overview::FIELDS_PER_SECTION);
    }

    public function getCrewBySlot($slot): array
    {
        // @todo refactor
        global $container;

        return $container->get(ShipCrewRepositoryInterface::class)->getByShipAndSlot(
            (int)$this->getId(),
            (int)$slot
        );
    }

    public function dockedOnTradePost(): bool
    {
        return $this->getDockedTo() && $this->getDockedTo()->getTradePostId() > 0;
    }

    public function getDockPrivileges(): array
    {
        if ($this->dockPrivileges === null) {
            // @todo refactor
            global $container;

            $this->dockPrivileges = $container->get(DockingPrivilegeRepositoryInterface::class)->getByShip(
                (int)$this->getId()
            );
        }
        return $this->dockPrivileges;
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
            if ($this->getSystemsId() == 0) {
                $this->mapfield = $container->get(MapRepositoryInterface::class)->getByCoordinates(
                    (int)$this->getCX(),
                    (int)$this->getCY()
                );
            } else {
                // @todo refactor
                global $container;

                $this->mapfield = $container->get(StarSystemMapRepositoryInterface::class)->getByCoordinates(
                    (int)$this->getSystemsId(),
                    (int)$this->getSX(),
                    (int)$this->getSY()
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

    public function canLandOnCurrentColony(): bool
    {
        if (!$this->getRump()->getGoodId()) {
            return false;
        }
        if (!$this->getCurrentColony()) {
            return false;
        }
        if (!$this->getCurrentColony()->ownedByCurrentUser()) {
            return false;
        }

        // @todo refactor
        global $container;
        return $container->get(ColonyLibFactoryInterface::class)
            ->createColonySurface($this->getCurrentColony())
            ->hasAirfield();
    }

    public function canBeAttacked(): bool
    {
        return !$this->ownedByCurrentUser() && !$this->getRump()->isTrumfield();
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
        if ($this->getCloakState()) {
            throw new AccessViolation($target->getId());
        }
        if ($colony === true) {
            if (!checkColonyPosition($target, $this) || $target->getId() == $this->getId()) {
                new AccessViolation($target->getId());
            }
            return true;

        } else {
            if (!checkPosition($this, $target)) {
                new AccessViolation($target->getId());
            }
        }
        if ($target->getShieldState() && $target->getUserId() != $this->getUserId()) {
            return false;
        }
        return true;
    }

    public function hasActiveWeapons(): bool
    {
        return $this->getPhaser() || $this->getTorpedos();
    }

    public function deactivateSystems(): void
    {
        $this->deactivateTraktorBeam();
        $this->setShieldState(false);
        $this->setNbs(false);
        $this->setLss(false);
        $this->setPhaser(false);
        $this->setTorpedos(false);
    }

    public function clearCache(): void
    {
        $this->rump = null;
        $this->storage = null;
    }

    public function getRump(): ShipRumpInterface
    {
        if ($this->rump === null) {
            // @todo refactor
            global $container;

            $this->rump = $container->get(ShipRumpRepositoryInterface::class)->find((int)$this->getRumpId());
        }
        return $this->rump;
    }

    public function hasPhaser(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER);
    }

    public function hasTorpedo(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO);
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
