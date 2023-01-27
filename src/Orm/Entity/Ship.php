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
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Map\MapEnum;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\ShipLSSModeEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Type\TorpedoStorageShipSystem;
use Stu\Component\Ship\System\Type\TroopQuartersShipSystem;
use Stu\Component\Station\StationUtility;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRepository")
 * @Table(
 *     name="stu_ships",
 *     indexes={
 *         @Index(name="ship_map_idx", columns={"map_id"}),
 *         @Index(name="ship_starsystem_map_idx", columns={"starsystem_map_id"}),
 *         @Index(name="outer_system_location_idx", columns={"cx","cy"}),
 *         @Index(name="ship_rump_idx", columns={"rumps_id"}),
 *         @Index(name="ship_web_idx", columns={"holding_web_id"}),
 *         @Index(name="ship_user_idx", columns={"user_id"})
 *     }
 * )
 **/
class Ship implements ShipInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $rumps_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $plans_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $fleets_id;

    /**
     * @Column(type="integer", length=5, nullable=true)
     *
     * @var int|null
     */
    private $layer_id = MapEnum::LAYER_ID_CRAGGANMORE;

    /**
     * @Column(type="integer", length=5)
     *
     * @var int
     */
    private $cx = 0;

    /**
     * @Column(type="integer", length=5)
     *
     * @var int
     */
    private $cy = 0;

    /**
     * @Column(type="smallint", length=1)
     *
     * @var int
     */
    private $direction = 0;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $name = '';

    /**
     * @Column(type="smallint", length=1)
     *
     * @var int
     */
    private $alvl = 0;

    /**
     * @Column(type="smallint", length=1)
     *
     * @var int
     */
    private $lss_mode = ShipLSSModeEnum::LSS_NORMAL;

    /**
     * @Column(type="integer", length=5)
     *
     * @var int
     */
    private $warpcore = 0;

    /**
     * @Column(type="integer", length=6)
     *
     * @var int
     */
    private $huelle = 0;

    /**
     * @Column(type="integer", length=6)
     *
     * @var int
     */
    private $max_huelle = 0;

    /**
     * @Column(type="integer", length=6)
     *
     * @var int
     */
    private $schilde = 0;

    /**
     * @Column(type="integer", length=6)
     *
     * @var int
     */
    private $max_schilde = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $tractored_ship_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $holding_web_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $dock;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $former_rumps_id = 0;

    /**
     * @Column(type="smallint", length=1, nullable=true)
     *
     * @var int|null
     */
    private $type = SpacecraftTypeEnum::SPACECRAFT_TYPE_SHIP;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $database_id = 0;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $is_destroyed = false;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $disabled = false;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $can_be_disabled = false;

    /**
     * @Column(type="smallint", length=3)
     *
     * @var int
     */
    private $hit_chance = 0;

    /**
     * @Column(type="smallint", length=3)
     *
     * @var int
     */
    private $evade_chance = 0;

    /**
     * @Column(type="smallint", length=4)
     *
     * @var int
     */
    private $reactor_output = 0;

    /**
     * @Column(type="smallint", length=4)
     *
     * @var int
     */
    private $base_damage = 0;

    /**
     * @Column(type="smallint", length=3)
     *
     * @var int
     */
    private $sensor_range = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $shield_regeneration_timer = 0;

    /**
     * @Column(type="smallint", length=3)
     *
     * @var int
     */
    private $state = ShipStateEnum::SHIP_STATE_NONE;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int|null
     */
    private $astro_start_turn;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $is_fleet_leader = false;

    /**
     * @Column(type="integer", nullable=true) *
     *
     * @var int|null
     */
    private $map_id;

    /**
     * @Column(type="integer", nullable=true) *
     *
     * @var int|null
     */
    private $starsystem_map_id;

    /**
     * @Column(type="integer", nullable=true) *
     *
     * @var int|null
     */
    private $influence_area_id;

    /**
     * @var null|FleetInterface
     *
     * @ManyToOne(targetEntity="Fleet", inversedBy="ships")
     * @JoinColumn(name="fleets_id", referencedColumnName="id")
     */
    private $fleet;

    /**
     * @var null|TradePostInterface
     *
     * @OneToOne(targetEntity="TradePost", mappedBy="ship")
     */
    private $tradePost;

    /**
     * @var null|ShipInterface
     *
     * @ManyToOne(targetEntity="Ship", inversedBy="dockedShips")
     * @JoinColumn(name="dock", referencedColumnName="id")
     */
    private $dockedTo;

    /**
     * @var ArrayCollection<int, ShipInterface>
     *
     * @OneToMany(targetEntity="Ship", mappedBy="dockedTo", indexBy="id")
     * @OrderBy({"fleets_id" = "DESC", "is_fleet_leader" = "DESC"})
     */
    private $dockedShips;

    /**
     * @var ArrayCollection<int, DockingPrivilegeInterface>
     *
     * @OneToMany(targetEntity="DockingPrivilege", mappedBy="ship")
     */
    private $dockingPrivileges;

    /**
     * @var null|ShipInterface
     *
     * @OneToOne(targetEntity="Ship")
     * @JoinColumn(name="tractored_ship_id", referencedColumnName="id")
     */
    private $tractoredShip;

    /**
     * @var null|ShipInterface
     *
     * @OneToOne(targetEntity="Ship", mappedBy="tractoredShip")
     */
    private $tractoringShip;

    /**
     * @var null|TholianWebInterface
     *
     * @ManyToOne(targetEntity="TholianWeb")
     * @JoinColumn(name="holding_web_id", referencedColumnName="id")
     */
    private $holdingWeb;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var ArrayCollection<int, ShipCrewInterface>
     *
     * @OneToMany(targetEntity="ShipCrew", mappedBy="ship", indexBy="id")
     * @OrderBy({"id" = "ASC"})
     */
    private $crew;

    /**
     * @var null|TorpedoStorageInterface
     *
     * @OneToOne(targetEntity="TorpedoStorage", mappedBy="ship")
     */
    private $torpedoStorage;

    /**
     * @var ArrayCollection<int, ShipSystemInterface>
     *
     * @OneToMany(targetEntity="ShipSystem", mappedBy="ship", indexBy="system_type", cascade={"remove"})
     * @OrderBy({"system_type" = "ASC"})
     */
    private $systems;

    /**
     * @var ShipRumpInterface
     *
     * @ManyToOne(targetEntity="ShipRump")
     * @JoinColumn(name="rumps_id", referencedColumnName="id")
     */
    private $rump;

    /**
     * @var null|ShipBuildplanInterface
     *
     * @ManyToOne(targetEntity="ShipBuildplan")
     * @JoinColumn(name="plans_id", referencedColumnName="id")
     */
    private $buildplan;

    /**
     * @var ArrayCollection<int, StorageInterface>
     *
     * @OneToMany(targetEntity="Storage", mappedBy="ship", indexBy="commodity_id")
     * @OrderBy({"commodity_id" = "ASC"})
     */
    private $storage;

    /**
     * @var null|MapInterface
     *
     * @ManyToOne(targetEntity="Map")
     * @JoinColumn(name="map_id", referencedColumnName="id")
     */
    private $map;

    /**
     * @var null|StarSystemMapInterface
     *
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id", referencedColumnName="id")
     */
    private $starsystem_map;

    /**
     * @var null|StarSystemInterface
     *
     * @ManyToOne(targetEntity="StarSystem")
     * @JoinColumn(name="influence_area_id", referencedColumnName="id")
     */
    private $influenceArea;

    /**
     * @var ArrayCollection<int, ShipLogInterface>
     *
     * @OneToMany(targetEntity="ShipLog", mappedBy="ship", fetch="EXTRA_LAZY")
     * @OrderBy({"id" = "DESC"})
     */
    private $logbook;

    public function __construct()
    {
        $this->dockedShips = new ArrayCollection();
        $this->dockingPrivileges = new ArrayCollection();
        $this->crew = new ArrayCollection();
        $this->systems = new ArrayCollection();
        $this->storage = new ArrayCollection();
        $this->logbook = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUserName(): string
    {
        return $this->getUser()->getUserName();
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
        return $this->getSystem() !== null ? $this->getSystem()->getId() : null;
    }

    public function getLayer(): ?LayerInterface
    {
        if ($this->getMap() !== null) {
            return $this->getMap()->getLayer();
        }

        return $this->getSystem()->getLayer();
    }

    public function getLayerId(): int
    {
        return $this->layer_id;
    }

    public function setLayerId(int $layerId): ShipInterface
    {
        $this->layer_id = $layerId;
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
        return $this->getStarsystemMap()->getSx();
    }

    public function getSy(): int
    {
        return $this->getStarsystemMap()->getSy();
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

    public function getLSSmode(): int
    {
        return $this->lss_mode;
    }

    public function isLSSModeNormal(): bool
    {
        return $this->getLSSMode() === ShipLSSModeEnum::LSS_NORMAL;
    }

    public function isLSSModeBorder(): bool
    {
        return $this->getLSSMode() === ShipLSSModeEnum::LSS_BORDER;
    }

    public function setLSSMode(int $lssMode): ShipInterface
    {
        $this->lss_mode = $lssMode;
        return $this;
    }

    public function getAlertState(): int
    {
        return $this->alvl;
    }

    public function setAlertState(int $alvl): ShipInterface
    {
        $this->alvl = $alvl;
        return $this;
    }

    public function setAlertStateGreen(): ShipInterface
    {
        return $this->setAlertState(ShipAlertStateEnum::ALERT_GREEN);
    }

    public function isSystemHealthy(int $systemId): bool
    {
        if (!$this->hasShipSystem($systemId)) {
            return false;
        }

        return $this->getShipSystem($systemId)->getStatus() > 0;
    }

    public function getSystemState(int $systemId): bool
    {
        if (!$this->hasShipSystem($systemId)) {
            return false;
        }

        return $this->getShipSystem($systemId)->getMode() === ShipSystemModeEnum::MODE_ON
            || $this->getShipSystem($systemId)->getMode() === ShipSystemModeEnum::MODE_ALWAYS_ON;
    }

    public function getImpulseState(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE);
    }

    public function getWarpState(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
    }

    public function getReactorLoad(): int
    {
        return $this->warpcore;
    }

    public function setReactorLoad(int $reactorload): ShipInterface
    {
        $this->warpcore = $reactorload;
        return $this;
    }

    public function getCloakState(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_CLOAK);
    }

    public function getTachyonState(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER);
    }

    public function getSubspaceState(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER);
    }

    public function getAstroState(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY);
    }

    public function getConstructionHubState(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB);
    }

    public function getHull(): int
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

    /**
     * proportional to shield system status
     */
    public function getMaxShield(): int
    {
        if (!$this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS)) {
            return $this->max_schilde;
        }

        return (int) (ceil($this->max_schilde
            * $this->getShipSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS)->getStatus() / 100));
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

    public function getNbs(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_NBS);
    }

    public function getLss(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_LSS);
    }

    public function getPhaserState(): bool
    {
        return $this->getSystemState(ShipSystemTypeEnum::SYSTEM_PHASER);
    }

    public function isAlertGreen(): bool
    {
        return $this->getAlertState() === ShipAlertStateEnum::ALERT_GREEN;
    }

    public function getTorpedoState(): bool
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
        if ($this->getTorpedoStorage() === null) {
            return 0;
        }

        return $this->getTorpedoStorage()->getStorage()->getAmount();
    }

    public function isBase(): bool
    {
        return $this->type === SpacecraftTypeEnum::SPACECRAFT_TYPE_STATION;
    }

    public function isTrumfield(): bool
    {
        return $this->getRump()->isTrumfield();
    }

    public function isShuttle(): bool
    {
        return $this->getRump()->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_SHUTTLE;
    }

    public function isConstruction(): bool
    {
        return $this->getRump()->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_CONSTRUCTION;
    }

    public function getSpacecraftType(): int
    {
        return $this->type;
    }

    public function setSpacecraftType(int $type): ShipInterface
    {
        $this->type = $type;
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

    public function isDestroyed(): bool
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

    /**
     * proportional to computer system status
     */
    public function getHitChance(): int
    {
        if (!$this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_COMPUTER)) {
            return $this->hit_chance;
        }

        return (int) (ceil($this->hit_chance
            * $this->getShipSystem(ShipSystemTypeEnum::SYSTEM_COMPUTER)->getStatus() / 100));
    }

    public function setHitChance(int $hitChance): ShipInterface
    {
        $this->hit_chance = $hitChance;
        return $this;
    }

    /**
     * proportional to impulsedrive system status
     */
    public function getEvadeChance(): int
    {
        if (!$this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)) {
            return $this->evade_chance;
        }

        return (int) (ceil($this->evade_chance
            * $this->getShipSystem(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)->getStatus() / 100));
    }

    public function setEvadeChance(int $evadeChance): ShipInterface
    {
        $this->evade_chance = $evadeChance;
        return $this;
    }

    /**
     * proportional to reactor system status
     */
    public function getReactorOutput(): int
    {
        $hasWarpcore = $this->hasWarpcore();
        $hasReactor = $this->hasFusionReactor();

        if (!$hasWarpcore && !$hasReactor) {
            return $this->reactor_output;
        }

        if ($hasReactor) {
            return (int) (ceil($this->reactor_output
                * $this->getShipSystem(ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR)->getStatus() / 100));
        } else {
            return (int) (ceil($this->reactor_output
                * $this->getShipSystem(ShipSystemTypeEnum::SYSTEM_WARPCORE)->getStatus() / 100));
        }
    }

    public function getTheoreticalReactorOutput(): int
    {
        return $this->reactor_output;
    }

    public function setReactorOutput(int $reactorOutput): ShipInterface
    {
        $this->reactor_output = $reactorOutput;
        return $this;
    }

    /**
     * proportional to energy weapon system status
     */
    public function getBaseDamage(): int
    {
        if (!$this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)) {
            return $this->base_damage;
        }

        return (int) (ceil($this->base_damage
            * $this->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->getStatus() / 100));
    }

    public function setBaseDamage(int $baseDamage): ShipInterface
    {
        $this->base_damage = $baseDamage;
        return $this;
    }

    /**
     * proportional to sensor system status
     */
    public function getSensorRange(): int
    {
        if (!$this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_LSS)) {
            return $this->sensor_range;
        }

        return (int) (ceil($this->sensor_range
            * $this->getShipSystem(ShipSystemTypeEnum::SYSTEM_LSS)->getStatus() / 100));
    }

    public function setSensorRange(int $sensorRange): ShipInterface
    {
        $this->sensor_range = $sensorRange;
        return $this;
    }

    /**
     * proportional to tractor beam system status
     */
    public function getTractorPayload(): int
    {
        if (!$this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)) {
            return 0;
        }

        return (int) (ceil($this->getRump()->getTractorPayload()
            * $this->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)->getStatus() / 100));
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

    public function isUnderRepair(): bool
    {
        return $this->getState() === ShipStateEnum::SHIP_STATE_REPAIR_ACTIVE
            || $this->getState() === ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE;
    }

    public function isInEmergency(): bool
    {
        return $this->getState() === ShipStateEnum::SHIP_STATE_EMERGENCY;
    }

    public function getAstroStartTurn(): ?int
    {
        return $this->astro_start_turn;
    }

    public function setAstroStartTurn(?int $turn): ShipInterface
    {
        $this->astro_start_turn = $turn;
        return $this;
    }

    public function getIsFleetLeader(): bool
    {
        return $this->getFleet() !== null && $this->is_fleet_leader;
    }

    public function setIsFleetLeader(bool $isFleetLeader): ShipInterface
    {
        $this->is_fleet_leader = $isFleetLeader;
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

    public function getMaxCrewCount(): int
    {
        $result = $this->getRump()->getMaxCrewCount();

        if ($this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
            if ($this->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE) {
                $result += TroopQuartersShipSystem::QUARTER_COUNT_BASE;
            } else {
                $result += TroopQuartersShipSystem::QUARTER_COUNT;
            }
        }
        return $result;
    }

    public function getExcessCrewCount(): int
    {
        return $this->getCrewCount() - $this->getBuildplan()->getCrew();
    }

    public function hasEnoughCrew(?GameControllerInterface $game = null): bool
    {
        if ($this->getBuildplan() === null) {
            if ($game !== null) {
                $game->addInformation(_("Keine Crew vorhanden"));
            }
            return false;
        }

        $result = $this->getBuildplan()->getCrew() <= 0
            || $this->getCrewCount() >= $this->getBuildplan()->getCrew();

        if (!$result) {
            if ($game !== null) {
                $game->addInformationf(
                    _("Es werden %d Crewmitglieder benötigt"),
                    $this->getBuildplan()->getCrew()
                );
            }
        }

        return $result;
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
        return $this->getIsFleetLeader();
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

    public function getSystem(): ?StarSystemInterface
    {
        return $this->getStarsystemMap() !== null ? $this->getStarsystemMap()->getSystem() : null;
    }

    public function getModules(): array
    {
        $modules = [];

        foreach ($this->getBuildplan()->getModules() as $obj) {
            $module = $obj->getModule();
            $index = $module->getType() === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL ? $module->getId() : $module->getType();
            $modules[$index] = $module;
        }

        if ($this->isBase()) {

            foreach ($this->getSystems() as $system) {
                $module = $system->getModule();

                if ($module !== null) {
                    $index = $module->getType() === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL ? $module->getId() : $module->getType();
                    $modules[$index] = $module;
                }
            }
        }

        return $modules;
    }

    public function getReactorCapacity(): int
    {
        if ($this->hasWarpcore()) {
            return $this->getTheoreticalReactorOutput() * ShipEnum::WARPCORE_CAPACITY_MULTIPLIER;
        }
        if ($this->hasFusionReactor()) {
            return $this->getTheoreticalReactorOutput() * ShipEnum::REACTOR_CAPACITY_MULTIPLIER;
        }

        return 0;
    }

    public function getReactorOutputCappedByReactorLoad(): int
    {
        if ($this->getReactorOutput() > $this->getReactorLoad()) {
            return $this->getReactorLoad();
        }
        return $this->getReactorOutput();
    }

    public function isWarpcoreHealthy(): bool
    {
        return $this->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_WARPCORE);
    }

    public function isDeflectorHealthy(): bool
    {
        return $this->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_DEFLECTOR);
    }

    public function isTroopQuartersHealthy(): bool
    {
        return $this->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS);
    }

    public function isMatrixScannerHealthy(): bool
    {
        return $this->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_MATRIX_SCANNER);
    }

    public function isTorpedoStorageHealthy(): bool
    {
        return $this->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE);
    }

    public function isShuttleRampHealthy(): bool
    {
        return $this->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP);
    }

    public function isWebEmitterHealthy(): bool
    {
        return $this->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB);
    }

    public function isWarpAble(): bool
    {
        return $this->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
    }

    public function isTractoring(): bool
    {
        return $this->getTractoredShip() !== null;
    }

    public function isTractored(): bool
    {
        return $this->getTractoringShip() !== null;
    }

    public function isOverColony(): ?ColonyInterface
    {
        return $this->getStarsystemMap() !== null ? $this->getStarsystemMap()->getColony() : null;
    }

    public function isOverSystem(): ?StarSystemInterface
    {
        if ($this->getSystem() !== null) {
            return null;
        }

        return $this->getMap()->getSystem();
    }

    public function isOverWormhole(): bool
    {
        return $this->getMap() !== null && $this->getMap()->getRandomWormholeEntry() !== null;
    }

    public function isWarpPossible(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE) && $this->getSystem() === null;
    }

    public function getTorpedo(): ?TorpedoTypeInterface
    {
        if ($this->getTorpedoStorage() === null) {
            return null;
        }

        return $this->getTorpedoStorage()->getTorpedo();
    }

    public function getTorpedoStorage(): ?TorpedoStorageInterface
    {
        return $this->torpedoStorage;
    }

    public function setTorpedoStorage(?TorpedoStorageInterface $torpedoStorage): ShipInterface
    {
        $this->torpedoStorage = $torpedoStorage;
        return $this;
    }

    public function getStorage(): Collection
    {
        return $this->storage;
    }

    public function getLogbook(): Collection
    {
        return $this->logbook;
    }

    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage()->getValues(),
            function (int $sum, StorageInterface $storage): int {
                return $sum + $storage->getAmount();
            },
            0
        );
    }

    public function getMaxStorage(): int
    {
        return $this->getRump()->getStorage();
    }

    public function getBeamableStorage(): array
    {
        return array_filter(
            $this->getStorage()->getValues(),
            function (StorageInterface $storage): bool {
                return $storage->getCommodity()->isBeamable() === true;
            }
        );
    }

    public function getTradePost(): ?TradePostInterface
    {
        return $this->tradePost;
    }

    public function setTradePost(?TradePostInterface $tradePost): ShipInterface
    {
        $this->tradePost = $tradePost;

        return $this;
    }

    public function getMap(): ?MapInterface
    {
        return $this->map;
    }

    public function updateLocation(?MapInterface $map, ?StarSystemMapInterface $starsystem_map): ShipInterface
    {
        $this->setMap($map);
        $this->setStarsystemMap($starsystem_map);

        return $this;
    }

    public function setMap(?MapInterface $map): ShipInterface
    {
        $this->map = $map;

        if ($map !== null) {
            $this->setLayerId($map->getLayer()->getId());
            $this->setCx($map->getCx());
            $this->setCy($map->getCy());
        }

        return $this;
    }

    public function getStarsystemMap(): ?StarSystemMapInterface
    {
        return $this->starsystem_map;
    }

    public function setStarsystemMap(?StarSystemMapInterface $starsystem_map): ShipInterface
    {
        $this->starsystem_map = $starsystem_map;

        if ($starsystem_map !== null) {
            $system = $starsystem_map->getSystem();
            if ($system->isWormhole()) {
                $this->setLayerId(MapEnum::LAYER_ID_WORMHOLES);
            } else {
                $this->setLayerId($system->getMapField()->getLayer()->getId());
            }
            $this->setCx($starsystem_map->getSystem()->getCx());
            $this->setCy($starsystem_map->getSystem()->getCy());
        }

        return $this;
    }

    public function getInfluenceArea(): ?StarSystemInterface
    {
        return $this->influenceArea;
    }

    public function setInfluenceArea(?StarSystemInterface $influenceArea): ShipInterface
    {
        $this->influenceArea = $influenceArea;
        return $this;
    }

    public function getBeamFactor(): int
    {
        return $this->getRump()->getBeamFactor();
    }

    public function getSectorString(): string
    {
        if ($this->getStarsystemMap() !== null) {
            return $this->getStarsystemMap()->getSectorString();
        } else {
            return $this->getMap()->getSectorString();
        }
    }

    public function getSectorId(): ?int
    {
        if ($this->getLayer() === null) {
            return null;
        }

        return $this->getLayer()->getSectorId($this->getMapCX(), $this->getMapCY());
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
    public function getShipSystem(int $systemType): ShipSystemInterface
    {
        /** @var ShipSystemInterface $system */
        $system = $this->getSystems()->get($systemType);
        return $system;
    }

    public function getHealthySystems(): array
    {
        $healthySystems = [];
        foreach ($this->getSystems() as $system) {
            if ($system->getStatus() > 0) {
                $healthySystems[] = $system;
            }
        }
        return $healthySystems;
    }

    public function displayNbsActions(): bool
    {
        return $this->getCloakState() == 0 && $this->getWarpstate() == 0;
    }

    public function tractorbeamNotPossible(): bool
    {
        return $this->isBase() || $this->getRump()->isTrumfield() || $this->getCloakState() || $this->getShieldState() || $this->getWarpState();
    }

    public function isInterceptAble(): bool
    {
        return $this->getWarpState();
    }

    public function getMapCX(): int
    {
        return (int) ceil($this->getCX() / MapEnum::FIELDS_PER_SECTION);
    }

    public function getMapCY(): int
    {
        return (int) ceil($this->getCY() / MapEnum::FIELDS_PER_SECTION);
    }

    public function dockedOnTradePost(): bool
    {
        return $this->getDockedTo() && $this->getDockedTo()->getTradePost() !== null;
    }

    public function getDockPrivileges(): Collection
    {
        return $this->dockingPrivileges;
    }

    public function getDockingSlotCount(): int
    {
        return ($this->getState() === ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION)
            || ($this->getState() === ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING)
            ? 50 : $this->getRump()->getDockingSlots();
    }

    public function hasFreeDockingSlots(): bool
    {
        return $this->getDockingSlotCount() > $this->getDockedShipCount();
    }

    public function getFreeDockingSlotCount(): int
    {
        return $this->getDockingSlotCount() - $this->getDockedShipCount();
    }

    public function getDockedShipCount(): int
    {
        return $this->dockedShips->count();
    }

    public function getTractoredShip(): ?ShipInterface
    {
        return $this->tractoredShip;
    }

    public function setTractoredShip(?ShipInterface $ship): ShipInterface
    {
        $this->tractoredShip = $ship;
        return $this;
    }

    public function setTractoredShipId(?int $shipId): ShipInterface
    {
        $this->tractored_ship_id = $shipId;
        return $this;
    }

    public function getTractoringShip(): ?ShipInterface
    {
        return $this->tractoringShip;
    }

    public function setTractoringShip(?ShipInterface $ship): ShipInterface
    {
        $this->tractoringShip = $ship;
        return $this;
    }

    public function getHoldingWeb(): ?TholianWebInterface
    {
        return $this->holdingWeb;
    }

    public function setHoldingWeb(?TholianWebInterface $web): ShipInterface
    {
        $this->holdingWeb = $web;

        if ($web === null) {
            $this->holding_web_id = null;
        }

        return $this;
    }

    public function getHoldingWebBackgroundStyle(): string
    {
        if ($this->getHoldingWeb() === null) {
            return '';
        }

        if ($this->getHoldingWeb()->isFinished()) {
            $icon =  'web.png';
        } else {
            $closeTofinish = $this->getHoldingWeb()->getFinishedTime() - time() < TimeConstants::ONE_HOUR_IN_SECONDS;

            if ($closeTofinish) {
                $icon = 'web_u.png';
            } else {
                $icon = 'web_u2.png';
            }
        }

        return sprintf('background-image: url(assets/buttons/%s); vertical-align: middle; text-align: center;', $icon);
    }

    public function getCurrentMapField()
    {
        return $this->getStarsystemMap() !== null ? $this->getStarsystemMap() : $this->getMap();
    }

    private function getShieldRegenerationPercentage(): int
    {
        return $this->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_SHIELDS) ? 10 : 0;
    }

    public function getShieldRegenerationRate(): int
    {
        return (int) ceil(($this->getMaxShield() / 100) * $this->getShieldRegenerationPercentage());
    }

    public function canIntercept(): bool
    {
        return !$this->isTractored() && !$this->isTractoring();
    }

    public function canMove(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)
            || $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE);
    }

    public function canBeAttacked(bool $checkWarpState = true): bool
    {
        return !$this->getRump()->isTrumfield() && (!$checkWarpState || !$this->getWarpState());
    }

    public function canAttack(): bool
    {
        return $this->getPhaserState() || $this->getTorpedoState();
    }

    public function hasEscapePods(): bool
    {
        return $this->getRump()->isEscapePods() && $this->getCrewCount() > 0;
    }

    public function getRepairRate(): int
    {
        // @todo
        return 100;
    }

    public function getRump(): ShipRumpInterface
    {
        return $this->rump;
    }

    public function getRumpId(): int
    {
        return $this->rumps_id;
    }

    public function getRumpName(): string
    {
        return $this->getRump()->getName();
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

    public function hasTachyonScanner(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER);
    }

    public function hasShuttleRamp(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP);
    }

    public function hasSubspaceScanner(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER);
    }

    public function hasAstroLaboratory(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY);
    }

    public function hasWarpcore(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPCORE);
    }

    public function hasWarpdrive(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
    }

    public function hasFusionReactor(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR);
    }

    public function hasNbsLss(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_COMPUTER);
    }

    public function hasUplink(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_UPLINK);
    }

    public function hasTranswarp(): bool
    {
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL);
    }

    public function getTranswarpCooldown(): ?int
    {
        $cooldown = $this->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL)->getCooldown();

        return $cooldown > time() ? $cooldown : null;
    }

    public function getMaxTorpedos(): int
    {
        return $this->getRump()->getBaseTorpedoStorage()
            + ($this->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)
                ? TorpedoStorageShipSystem::TORPEDO_CAPACITY : 0);
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

    public function setDockedToId(?int $dockedToId): ShipInterface
    {
        $this->dock = $dockedToId;
        return $this;
    }

    public function hasFreeShuttleSpace(?LoggerUtilInterface $loggerUtil = null): bool
    {
        if ($loggerUtil !== null) {
            $loggerUtil->log(sprintf('rumpShuttleSlots: %d', $this->getRump()->getShuttleSlots()));
            $loggerUtil->log(sprintf('storedShuttleCount: %d', $this->getStoredShuttleCount()));
        }
        return $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP)
            && $this->getRump()->getShuttleSlots() - $this->getStoredShuttleCount() > 0;
    }

    public function getStoredShuttles(): array
    {
        $shuttles = [];

        foreach ($this->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                $shuttles[] = $stor->getCommodity();
            }
        }

        return $shuttles;
    }

    public function getStoredShuttleCount(): int
    {
        $count = 0;

        foreach ($this->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                $count += $stor->getAmount();
            }
        }

        return $count;
    }

    public function canMan(): bool
    {
        return $this->getCrewCount() === 0
            && $this->getBuildplan() !== null
            && $this->getBuildplan()->getCrew() > 0
            && $this->hasShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT);
    }

    public function canBuildConstruction(): bool
    {
        return StationUtility::canShipBuildConstruction($this);
    }

    public function hasCrewmanOfUser(int $userId): bool
    {
        foreach ($this->getCrewlist() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser()->getId() === $userId) {
                return true;
            }
        }

        return false;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getHullColorStyle(): string
    {
        return $this->getColorStyle($this->getHull(), $this->getMaxHuell());
    }

    private function getColorStyle(int $actual, int $max): string
    {
        // full
        if ($actual == $max) {
            return 'color: #dddddd;';
        }

        // less than 100% - green
        if ($actual / $max > 0.75) {
            return 'color: #19c100;';
        }

        // less than 75% - yellow
        if ($actual / $max > 0.50) {
            return 'color: #f4e932;';
        }

        // less than 50% - orange
        if ($actual / $max > 0.25) {
            return 'color: #f48b28;';
        }

        // less than 25% - red
        return 'color: #ff3c3c;';
    }
}
