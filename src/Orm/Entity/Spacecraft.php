<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Override;
use RuntimeException;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Map\DirectionEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\TorpedoStorageShipSystem;
use Stu\Component\Spacecraft\System\Type\TractorBeamShipSystem;
use Stu\Lib\Transfer\CommodityTransfer;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLib;
use Stu\Orm\Repository\SpacecraftRepository;

#[Table(name: 'stu_spacecraft')]
#[Entity(repositoryClass: SpacecraftRepository::class)]
#[InheritanceType('JOINED')]
#[DiscriminatorColumn(name: 'type', type: 'string')]
#[DiscriminatorMap([
    SpacecraftTypeEnum::SHIP->value => Ship::class,
    SpacecraftTypeEnum::STATION->value => Station::class,
    SpacecraftTypeEnum::THOLIAN_WEB->value => TholianWeb::class
])]
abstract class Spacecraft implements SpacecraftInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $plan_id = null;

    #[Column(type: 'smallint', length: 1, enumType: DirectionEnum::class, nullable: true)]
    private ?DirectionEnum $direction = null;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'smallint', length: 1, enumType: SpacecraftAlertStateEnum::class)]
    private SpacecraftAlertStateEnum $alvl = SpacecraftAlertStateEnum::ALERT_GREEN;

    #[Column(type: 'smallint', length: 1, enumType: SpacecraftLssModeEnum::class)]
    private SpacecraftLssModeEnum $lss_mode = SpacecraftLssModeEnum::NORMAL;

    #[Column(type: 'integer', length: 6)]
    private int $huelle = 0;

    #[Column(type: 'integer', length: 6)]
    private int $max_huelle = 0;

    #[Column(type: 'integer', length: 6)]
    private int $schilde = 0;

    #[Column(type: 'integer', length: 6)]
    private int $max_schilde = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $tractored_ship_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $holding_web_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $database_id = null;

    private bool $is_destroyed = false;

    #[Column(type: 'boolean')]
    private bool $disabled = false;

    #[Column(type: 'smallint', length: 3)]
    private int $hit_chance = 0;

    #[Column(type: 'smallint', length: 3)]
    private int $evade_chance = 0;

    #[Column(type: 'smallint', length: 4)]
    private int $base_damage = 0;

    #[Column(type: 'smallint', enumType: SpacecraftStateEnum::class)]
    private SpacecraftStateEnum $state = SpacecraftStateEnum::NONE;

    #[Column(type: 'integer')]
    private int $location_id = 0;

    #[Column(type: 'boolean')]
    private bool $in_emergency = false;

    #[OneToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'tractored_ship_id', referencedColumnName: 'id')]
    private ?ShipInterface $tractoredShip = null;

    #[ManyToOne(targetEntity: 'TholianWeb')]
    #[JoinColumn(name: 'holding_web_id', referencedColumnName: 'id')]
    private ?TholianWebInterface $holdingWeb = null;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    /**
     * @var ArrayCollection<int, CrewAssignmentInterface>
     */
    #[OneToMany(targetEntity: 'CrewAssignment', mappedBy: 'spacecraft', indexBy: 'id')]
    #[OrderBy(['id' => 'ASC'])]
    private Collection $crew;

    #[OneToOne(targetEntity: 'TorpedoStorage', mappedBy: 'spacecraft')]
    private ?TorpedoStorageInterface $torpedoStorage = null;

    /**
     * @var ArrayCollection<int, SpacecraftSystemInterface>
     */
    #[OneToMany(targetEntity: 'SpacecraftSystem', mappedBy: 'spacecraft', indexBy: 'system_type')]
    #[OrderBy(['system_type' => 'ASC'])]
    private Collection $systems;

    #[ManyToOne(targetEntity: 'SpacecraftRump')]
    #[JoinColumn(name: 'rump_id', referencedColumnName: 'id')]
    private SpacecraftRumpInterface $rump;

    #[ManyToOne(targetEntity: 'SpacecraftBuildplan')]
    #[JoinColumn(name: 'plan_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?SpacecraftBuildplanInterface $buildplan = null;

    /**
     * @var ArrayCollection<int, StorageInterface>
     */
    #[OneToMany(targetEntity: 'Storage', mappedBy: 'spacecraft', indexBy: 'commodity_id')]
    #[OrderBy(['commodity_id' => 'ASC'])]
    private Collection $storage;

    #[ManyToOne(targetEntity: 'Location')]
    #[JoinColumn(name: 'location_id', referencedColumnName: 'id')]
    private LocationInterface $location;

    /**
     * @var ArrayCollection<int, ShipLogInterface>
     */
    #[OneToMany(targetEntity: 'ShipLog', mappedBy: 'spacecraft', fetch: 'EXTRA_LAZY')]
    #[OrderBy(['id' => 'DESC'])]
    private Collection $logbook;

    #[OneToOne(targetEntity: 'ShipTakeover', mappedBy: 'source')]
    private ?ShipTakeoverInterface $takeoverActive = null;

    #[OneToOne(targetEntity: 'ShipTakeover', mappedBy: 'target')]
    private ?ShipTakeoverInterface $takeoverPassive = null;

    public function __construct()
    {
        $this->crew = new ArrayCollection();
        $this->systems = new ArrayCollection();
        $this->storage = new ArrayCollection();
        $this->logbook = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        if ($this->id === null) {
            throw new RuntimeException(sprintf('entity "%s" not yet persisted', $this->getName()));
        }

        return $this->id;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getUserName(): string
    {
        return $this->getUser()->getName();
    }

    #[Override]
    public function getSystemsId(): ?int
    {
        return $this->getSystem() !== null ? $this->getSystem()->getId() : null;
    }

    #[Override]
    public function getLayer(): ?LayerInterface
    {
        return $this->getLocation()->getLayer();
    }

    #[Override]
    public function getFlightDirection(): ?DirectionEnum
    {
        return $this->direction;
    }

    #[Override]
    public function setFlightDirection(DirectionEnum $direction): SpacecraftInterface
    {
        $this->direction = $direction;
        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): SpacecraftInterface
    {
        $this->name = $name;
        return $this;
    }

    #[Override]
    public function getLssMode(): SpacecraftLssModeEnum
    {
        return $this->lss_mode;
    }

    #[Override]
    public function setLssMode(SpacecraftLssModeEnum $lssMode): SpacecraftInterface
    {
        $this->lss_mode = $lssMode;
        return $this;
    }

    #[Override]
    public function getAlertState(): SpacecraftAlertStateEnum
    {
        return $this->alvl;
    }

    #[Override]
    public function setAlertState(SpacecraftAlertStateEnum $state): SpacecraftInterface
    {
        $this->alvl = $state;
        return $this;
    }

    #[Override]
    public function setAlertStateGreen(): SpacecraftInterface
    {
        return $this->setAlertState(SpacecraftAlertStateEnum::ALERT_GREEN);
    }

    #[Override]
    public function isSystemHealthy(SpacecraftSystemTypeEnum $type): bool
    {
        if (!$this->hasSpacecraftSystem($type)) {
            return false;
        }

        return $this->getSpacecraftSystem($type)->isHealthy();
    }

    #[Override]
    public function getSystemState(SpacecraftSystemTypeEnum $type): bool
    {
        if (!$this->hasSpacecraftSystem($type)) {
            return false;
        }

        return $this->getSpacecraftSystem($type)->getMode()->isActivated();
    }

    #[Override]
    public function getImpulseState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::IMPULSEDRIVE);
    }

    #[Override]
    public function getWarpDriveState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::WARPDRIVE);
    }

    #[Override]
    public function isWarped(): bool
    {
        return $this->getWarpDriveState();
    }

    #[Override]
    public function isHeldByTholianWeb(): bool
    {
        return $this->getHoldingWeb() !== null;
    }

    #[Override]
    public function isCloaked(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::CLOAK);
    }

    #[Override]
    public function getTachyonState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::TACHYON_SCANNER);
    }

    #[Override]
    public function getSubspaceState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::SUBSPACE_SCANNER);
    }

    #[Override]
    public function getRPGModuleState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::RPG_MODULE);
    }

    #[Override]
    public function getHull(): int
    {
        return $this->huelle;
    }

    #[Override]
    public function setHuell(int $hull): SpacecraftInterface
    {
        $this->huelle = $hull;
        return $this;
    }

    #[Override]
    public function getMaxHull(): int
    {
        return $this->max_huelle;
    }

    #[Override]
    public function setMaxHuell(int $maxHull): SpacecraftInterface
    {
        $this->max_huelle = $maxHull;
        return $this;
    }

    #[Override]
    public function getShield(): int
    {
        return $this->schilde;
    }

    #[Override]
    public function setShield(int $schilde): SpacecraftInterface
    {
        $this->schilde = $schilde;
        return $this;
    }

    /**
     * proportional to shield system status
     */
    #[Override]
    public function getMaxShield(bool $isTheoretical = false): int
    {
        if ($isTheoretical || !$this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)) {
            return $this->max_schilde;
        }

        return (int) (ceil($this->max_schilde
            * $this->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus() / 100));
    }

    #[Override]
    public function setMaxShield(int $maxShields): SpacecraftInterface
    {
        $this->max_schilde = $maxShields;
        return $this;
    }

    #[Override]
    public function getHealthPercentage(): float
    {
        return ($this->getHull() + $this->getShield())
            / ($this->getMaxHull() + $this->getMaxShield(true)) * 100;
    }

    #[Override]
    public function isShielded(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::SHIELDS);
    }

    #[Override]
    public function getNbs(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::NBS);
    }

    #[Override]
    public function getLss(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::LSS);
    }

    #[Override]
    public function getPhaserState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::PHASER);
    }

    #[Override]
    public function isAlertGreen(): bool
    {
        return $this->getAlertState() === SpacecraftAlertStateEnum::ALERT_GREEN;
    }

    #[Override]
    public function getTorpedoState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::TORPEDO);
    }

    #[Override]
    public function getTorpedoCount(): int
    {
        if ($this->getTorpedoStorage() === null) {
            return 0;
        }

        return $this->getTorpedoStorage()->getStorage()->getAmount();
    }

    #[Override]
    public function isStation(): bool
    {
        return $this instanceof StationInterface;
    }

    #[Override]
    public function isShuttle(): bool
    {
        return $this->getRump()->getCategoryId() === SpacecraftRumpEnum::SHIP_CATEGORY_SHUTTLE;
    }

    #[Override]
    public function isConstruction(): bool
    {
        return $this->getRump()->getCategoryId() === SpacecraftRumpEnum::SHIP_CATEGORY_CONSTRUCTION;
    }

    #[Override]
    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    #[Override]
    public function setDatabaseId(?int $databaseEntryId): SpacecraftInterface
    {
        $this->database_id = $databaseEntryId;
        return $this;
    }

    #[Override]
    public function isDestroyed(): bool
    {
        return $this->is_destroyed;
    }

    #[Override]
    public function setIsDestroyed(bool $isDestroyed): SpacecraftInterface
    {
        $this->is_destroyed = $isDestroyed;
        return $this;
    }

    #[Override]
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    #[Override]
    public function setDisabled(bool $isDisabled): SpacecraftInterface
    {
        $this->disabled = $isDisabled;
        return $this;
    }



    /**
     * proportional to computer system status
     */
    #[Override]
    public function getHitChance(): int
    {
        if (!$this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::COMPUTER)) {
            return $this->hit_chance;
        }

        return (int) (ceil($this->hit_chance
            * $this->getSpacecraftSystem(SpacecraftSystemTypeEnum::COMPUTER)->getStatus() / 100));
    }

    #[Override]
    public function setHitChance(int $hitChance): SpacecraftInterface
    {
        $this->hit_chance = $hitChance;
        return $this;
    }

    /**
     * proportional to impulsedrive system status
     */
    #[Override]
    public function getEvadeChance(): int
    {
        if (!$this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::IMPULSEDRIVE)) {
            return $this->evade_chance;
        }

        return (int) (ceil($this->evade_chance
            * $this->getSpacecraftSystem(SpacecraftSystemTypeEnum::IMPULSEDRIVE)->getStatus() / 100));
    }

    #[Override]
    public function setEvadeChance(int $evadeChance): SpacecraftInterface
    {
        $this->evade_chance = $evadeChance;
        return $this;
    }

    /**
     * proportional to energy weapon system status
     */
    #[Override]
    public function getBaseDamage(): int
    {
        if (!$this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)) {
            return $this->base_damage;
        }

        return (int) (ceil($this->base_damage
            * $this->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->getStatus() / 100));
    }

    #[Override]
    public function setBaseDamage(int $baseDamage): SpacecraftInterface
    {
        $this->base_damage = $baseDamage;
        return $this;
    }

    /**
     * proportional to tractor beam system status
     */
    #[Override]
    public function getTractorPayload(): int
    {
        if (!$this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)) {
            return 0;
        }

        return (int) (ceil($this->getRump()->getTractorPayload()
            * $this->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->getStatus() / 100));
    }

    #[Override]
    public function getState(): SpacecraftStateEnum
    {
        return $this->state;
    }

    #[Override]
    public function setState(SpacecraftStateEnum $state): SpacecraftInterface
    {
        $this->state = $state;
        return $this;
    }

    #[Override]
    public function isInEmergency(): bool
    {
        return $this->in_emergency;
    }

    #[Override]
    public function setIsInEmergency(bool $inEmergency): SpacecraftInterface
    {
        $this->in_emergency = $inEmergency;
        return $this;
    }

    #[Override]
    public function isUnderRepair(): bool
    {
        return $this->getState() === SpacecraftStateEnum::REPAIR_ACTIVE
            || $this->getState() === SpacecraftStateEnum::REPAIR_PASSIVE;
    }

    #[Override]
    public function getCrewAssignments(): Collection
    {
        return $this->crew;
    }

    #[Override]
    public function getPosX(): int
    {
        return $this->location->getX();
    }

    #[Override]
    public function getPosY(): int
    {
        return $this->location->getY();
    }

    #[Override]
    public function getCrewCount(): int
    {
        return $this->getCrewAssignments()->count();
    }

    #[Override]
    public function getNeededCrewCount(): int
    {
        $buildplan = $this->getBuildplan();
        if ($buildplan === null) {
            return 0;
        }

        return $buildplan->getCrew();
    }

    #[Override]
    public function getExcessCrewCount(): int
    {
        return $this->getCrewCount() - $this->getNeededCrewCount();
    }

    #[Override]
    public function hasEnoughCrew(?GameControllerInterface $game = null): bool
    {
        $buildplan = $this->getBuildplan();

        if ($buildplan === null) {
            if ($game !== null) {
                $game->addInformation(_("Keine Crew vorhanden"));
            }
            return false;
        }

        $result = $buildplan->getCrew() <= 0
            || $this->getCrewCount() >= $buildplan->getCrew();

        if (!$result && $game !== null) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $buildplan->getCrew()
            );
        }

        return $result;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): SpacecraftInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getSystem(): ?StarSystemInterface
    {
        return $this->getStarsystemMap() !== null ? $this->getStarsystemMap()->getSystem() : null;
    }

    #[Override]
    public function getModules(): array
    {
        $modules = [];

        $buildplan = $this->getBuildplan();
        if ($buildplan === null) {
            return $modules;
        }

        foreach ($buildplan->getModulesOrdered() as $obj) {
            $module = $obj->getModule();
            $index = $module->getType() === SpacecraftModuleTypeEnum::SPECIAL ? $module->getId() : $module->getType()->value;
            $modules[$index] = $module;
        }

        return $modules;
    }

    #[Override]
    public function isDeflectorHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::DEFLECTOR);
    }

    #[Override]
    public function isMatrixScannerHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::MATRIX_SCANNER);
    }

    #[Override]
    public function isTorpedoStorageHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::TORPEDO_STORAGE);
    }

    #[Override]
    public function isShuttleRampHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::SHUTTLE_RAMP);
    }

    #[Override]
    public function isWebEmitterHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::THOLIAN_WEB);
    }

    #[Override]
    public function isTractoring(): bool
    {
        return $this->getTractoredShip() !== null;
    }

    #[Override]
    public function isOverColony(): ?ColonyInterface
    {
        return $this->getStarsystemMap() !== null ? $this->getStarsystemMap()->getColony() : null;
    }

    #[Override]
    public function isOverSystem(): ?StarSystemInterface
    {
        $location = $this->getLocation();
        if ($location instanceof StarSystemMapInterface) {
            return null;
        }

        return $location->getSystem();
    }

    #[Override]
    public function isWarpPossible(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE) && $this->getSystem() === null;
    }

    #[Override]
    public function getTorpedo(): ?TorpedoTypeInterface
    {
        if ($this->getTorpedoStorage() === null) {
            return null;
        }

        return $this->getTorpedoStorage()->getTorpedo();
    }

    #[Override]
    public function getTorpedoStorage(): ?TorpedoStorageInterface
    {
        return $this->torpedoStorage;
    }

    #[Override]
    public function setTorpedoStorage(?TorpedoStorageInterface $torpedoStorage): SpacecraftInterface
    {
        $this->torpedoStorage = $torpedoStorage;
        return $this;
    }

    #[Override]
    public function getStorage(): Collection
    {
        return $this->storage;
    }

    #[Override]
    public function getLogbook(): Collection
    {
        return $this->logbook;
    }

    #[Override]
    public function getTakeoverActive(): ?ShipTakeoverInterface
    {
        return $this->takeoverActive;
    }

    #[Override]
    public function setTakeoverActive(?ShipTakeoverInterface $takeover): SpacecraftInterface
    {
        $this->takeoverActive = $takeover;

        return $this;
    }

    #[Override]
    public function getTakeoverPassive(): ?ShipTakeoverInterface
    {
        return $this->takeoverPassive;
    }

    #[Override]
    public function setTakeoverPassive(?ShipTakeoverInterface $takeover): SpacecraftInterface
    {
        $this->takeoverPassive = $takeover;

        return $this;
    }

    #[Override]
    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage()->getValues(),
            fn(int $sum, StorageInterface $storage): int => $sum + $storage->getAmount(),
            0
        );
    }

    #[Override]
    public function getMaxStorage(): int
    {
        return $this->getRump()->getStorage();
    }

    #[Override]
    public function getBeamableStorage(): Collection
    {
        return CommodityTransfer::excludeNonBeamable($this->storage);
    }

    #[Override]
    public function getMap(): ?MapInterface
    {
        if ($this->location instanceof MapInterface) {
            return $this->location;
        }
        if ($this->location instanceof StarSystemMapInterface) {
            return $this->location->getSystem()->getMap();
        }

        return null;
    }

    #[Override]
    public function getMapRegion(): ?MapRegionInterface
    {
        $systemMap = $this->getStarsystemMap();
        if ($systemMap !== null) {
            return null;
        }

        $map = $this->getMap();
        if ($map === null) {
            return null;
        }

        return $map->getMapRegion();
    }

    #[Override]
    public function setLocation(LocationInterface $location): SpacecraftInterface
    {
        $this->location = $location;

        return $this;
    }

    #[Override]
    public function getStarsystemMap(): ?StarSystemMapInterface
    {
        if ($this->location instanceof StarSystemMapInterface) {
            return $this->location;
        }

        return null;
    }

    #[Override]
    public function getLocation(): MapInterface|StarSystemMapInterface
    {
        if (
            $this->location instanceof MapInterface
            || $this->location instanceof StarSystemMapInterface
        ) {
            return $this->location;
        }

        throw new RuntimeException('unknown type');
    }

    #[Override]
    public function getBeamFactor(): int
    {
        return $this->getRump()->getBeamFactor();
    }

    #[Override]
    public function getSectorString(): string
    {
        return $this->getLocation()->getSectorString();
    }

    #[Override]
    public function getBuildplan(): ?SpacecraftBuildplanInterface
    {
        return $this->buildplan;
    }

    #[Override]
    public function setBuildplan(?SpacecraftBuildplanInterface $spacecraftBuildplan): SpacecraftInterface
    {
        $this->buildplan = $spacecraftBuildplan;
        return $this;
    }

    #[Override]
    public function getSystems(): Collection
    {
        return $this->systems;
    }

    #[Override]
    public function hasSpacecraftSystem(SpacecraftSystemTypeEnum $type): bool
    {
        return $this->getSystems()->containsKey($type->value);
    }

    #[Override]
    public function getSpacecraftSystem(SpacecraftSystemTypeEnum $type): SpacecraftSystemInterface
    {
        $system = $this->getSystems()->get($type->value);
        if ($system === null) {
            throw new RuntimeException(sprintf('system type %d does not exist on ship', $type->value));
        }

        return $system;
    }

    #[Override]
    public function displayNbsActions(): bool
    {
        return !$this->isCloaked()
            && !$this->isWarped();
    }

    #[Override]
    public function isTractorbeamPossible(): bool
    {
        return TractorBeamShipSystem::isTractorBeamPossible($this);
    }

    #[Override]
    public function isBoardingPossible(): bool
    {
        return FightLib::isBoardingPossible($this);
    }

    #[Override]
    public function isInterceptable(): bool
    {
        //TODO can tractored ships be intercepted?!
        return $this->getWarpDriveState();
    }

    #[Override]
    public function getTractoredShip(): ?ShipInterface
    {
        return $this->tractoredShip;
    }

    #[Override]
    public function setTractoredShip(?ShipInterface $ship): SpacecraftInterface
    {
        $this->tractoredShip = $ship;
        return $this;
    }

    #[Override]
    public function getHoldingWeb(): ?TholianWebInterface
    {
        return $this->holdingWeb;
    }

    #[Override]
    public function setHoldingWeb(?TholianWebInterface $web): SpacecraftInterface
    {
        $this->holdingWeb = $web;

        return $this;
    }

    #[Override]
    public function getHoldingWebBackgroundStyle(): string
    {
        if ($this->getHoldingWeb() === null) {
            return '';
        }

        if ($this->getHoldingWeb()->isFinished()) {
            $icon =  'web.png';
        } else {
            $closeTofinish = $this->getHoldingWeb()->getFinishedTime() - time() < TimeConstants::ONE_HOUR_IN_SECONDS;

            $icon = $closeTofinish ? 'web_u.png' : 'web_u2.png';
        }

        return sprintf('src="assets/buttons/%s"; class="indexedGraphics" style="z-index: 5;"', $icon);
    }

    public function getHoldingWebImageStyle(): string
    {
        if ($this->getHoldingWeb() === null) {
            return '';
        }

        if ($this->getHoldingWeb()->isFinished()) {
            $icon =  'webfill.png';
        } else {
            $closeTofinish = $this->getHoldingWeb()->getFinishedTime() - time() < TimeConstants::ONE_HOUR_IN_SECONDS;

            $icon = $closeTofinish ? 'web_ufill.png' : 'web_ufill2.png';
        }

        return $icon;
    }

    #[Override]
    public function canIntercept(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::WARPDRIVE)
            && !$this->isTractoring()
            && (!$this instanceof ShipInterface || !$this->isTractored());
    }

    #[Override]
    public function canMove(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)
            || $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::IMPULSEDRIVE);
    }

    #[Override]
    public function hasActiveWeapon(): bool
    {
        return $this->getPhaserState() || $this->getTorpedoState();
    }

    #[Override]
    public function hasEscapePods(): bool
    {
        return $this->getRump()->isEscapePods() && $this->getCrewCount() > 0;
    }

    #[Override]
    public function getRepairRate(): int
    {
        // @todo
        return 100;
    }

    #[Override]
    public function getRump(): SpacecraftRumpInterface
    {
        return $this->rump;
    }

    #[Override]
    public function getRumpId(): int
    {
        return $this->getRump()->getId();
    }

    #[Override]
    public function getRumpName(): string
    {
        return $this->getRump()->getName();
    }

    #[Override]
    public function setRump(SpacecraftRumpInterface $shipRump): SpacecraftInterface
    {
        $this->rump = $shipRump;
        return $this;
    }

    #[Override]
    public function hasPhaser(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER);
    }

    #[Override]
    public function hasTorpedo(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO);
    }

    #[Override]
    public function hasCloak(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::CLOAK);
    }

    #[Override]
    public function hasShuttleRamp(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHUTTLE_RAMP);
    }

    #[Override]
    public function hasWarpdrive(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE);
    }

    #[Override]
    public function hasReactor(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPCORE) ||
            $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::FUSION_REACTOR) ||
            $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SINGULARITY_REACTOR);
    }

    #[Override]
    public function hasNbsLss(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::LSS);
    }

    #[Override]
    public function hasUplink(): bool
    {
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::UPLINK);
    }

    #[Override]
    public function getMaxTorpedos(): int
    {
        return $this->getRump()->getBaseTorpedoStorage()
            + ($this->isSystemHealthy(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
                ? TorpedoStorageShipSystem::TORPEDO_CAPACITY : 0);
    }

    #[Override]
    public function hasFreeShuttleSpace(?LoggerUtilInterface $loggerUtil = null): bool
    {
        if ($loggerUtil !== null) {
            $loggerUtil->log(sprintf('rumpShuttleSlots: %d', $this->getRump()->getShuttleSlots()));
            $loggerUtil->log(sprintf('storedShuttleCount: %d', $this->getStoredShuttleCount()));
        }
        return $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)
            && $this->getRump()->getShuttleSlots() - $this->getStoredShuttleCount() > 0;
    }

    #[Override]
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

    #[Override]
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

    /**
     * @return CommodityInterface[]
     */
    #[Override]
    public function getStoredBuoy(): array
    {
        $buoy = [];

        foreach ($this->getStorage() as $stor) {
            if ($stor->getCommodity()->isBouy()) {
                $buoy[] = $stor->getCommodity();
            }
        }

        return $buoy;
    }


    #[Override]
    public function hasStoredBuoy(): bool
    {
        return $this->getStoredBuoy() !== [];
    }

    #[Override]
    public function canMan(): bool
    {
        $buildplan = $this->getBuildplan();

        return $buildplan !== null
            && $buildplan->getCrew() > 0
            && $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::LIFE_SUPPORT);
    }

    #[Override]
    public function hasCrewmanOfUser(int $userId): bool
    {
        foreach ($this->getCrewAssignments() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser()->getId() === $userId) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function getHref(): string
    {
        $moduleView = $this->getType()->getModuleView();
        if ($moduleView === null) {
            return '';
        }

        return sprintf(
            '%s?%s=1&id=%d',
            $moduleView->getPhpPage(),
            $this->getType()->getViewIdentifier(),
            $this->getId()
        );
    }

    #[Override]
    public function __toString(): string
    {
        if ($this->id !== null) {
            return sprintf(
                "id: %d, name: %s,\nhull: %d/%d, shields %d/%d,\nevadeChance: %d, hitChance: %d, baseDamage: %d",
                $this->getId(),
                $this->getName(),
                $this->huelle,
                $this->max_huelle,
                $this->schilde,
                $this->max_schilde,
                $this->evade_chance,
                $this->hit_chance,
                $this->base_damage
            );
        }

        return $this->getName();
    }

    #[Override]
    public function getHullColorStyle(): string
    {
        return $this->getColorStyle($this->getHull(), $this->getMaxHull());
    }

    private function getColorStyle(int $actual, int $max): string
    {
        // full
        if ($actual === $max) {
            return '';
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
