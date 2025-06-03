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
use Stu\Component\Map\DirectionEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\Trait\SpacecraftSystemExistenceTrait;
use Stu\Component\Spacecraft\Trait\SpacecrafCharacteristicsTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftCrewTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftEvadeChanceTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftHitChanceTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftHoldingWebTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftHullColorStyleTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftInteractionTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftLocationTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftShieldsTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftStateTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftStorageTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftSystemHealthTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftSystemStateTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftTorpedoTrait;
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
    use SpacecraftSystemStateTrait;
    use SpacecraftSystemExistenceTrait;
    use SpacecraftSystemHealthTrait;
    use SpacecraftShieldsTrait;
    use SpacecraftHitChanceTrait;
    use SpacecraftEvadeChanceTrait;
    use SpacecraftCrewTrait;
    use SpacecraftLocationTrait;
    use SpacecraftStorageTrait;
    use SpacecraftInteractionTrait;
    use SpacecraftHoldingWebTrait;
    use SpacecraftHullColorStyleTrait;
    use SpacecraftTorpedoTrait;
    use SpacecrafCharacteristicsTrait;
    use SpacecraftStateTrait;

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

    #[Override]
    public function setMaxShield(int $maxShields): SpacecraftInterface
    {
        $this->max_schilde = $maxShields;
        return $this;
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

    #[Override]
    public function setHitChance(int $hitChance): SpacecraftInterface
    {
        $this->hit_chance = $hitChance;
        return $this;
    }

    #[Override]
    public function setEvadeChance(int $evadeChance): SpacecraftInterface
    {
        $this->evade_chance = $evadeChance;
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
    public function getCrewAssignments(): Collection
    {
        return $this->crew;
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
    public function setLocation(LocationInterface $location): SpacecraftInterface
    {
        $this->location = $location;

        return $this;
    }

    #[Override]
    public function getBeamFactor(): int
    {
        return $this->getRump()->getBeamFactor();
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
                "id: %d, name: %s,\nhull: %d/%d, shields %d/%d,\nevadeChance: %d, hitChance: %d",
                $this->getId(),
                $this->getName(),
                $this->huelle,
                $this->max_huelle,
                $this->schilde,
                $this->max_schilde,
                $this->evade_chance,
                $this->hit_chance
            );
        }

        return $this->getName();
    }
}
