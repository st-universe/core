<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use BadMethodCallException;
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
use LogicException;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\Trait\SpacecraftSystemExistenceTrait;
use Stu\Component\Spacecraft\Trait\SpacecrafCharacteristicsTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftCrewTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftHoldingWebTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftHrefTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftHullColorStyleTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftInteractionTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftLocationTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftShieldsTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftStateTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftStorageTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftSystemHealthTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftSystemStateTrait;
use Stu\Component\Spacecraft\Trait\SpacecraftTorpedoTrait;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Lib\Map\EntityWithLocationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
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
abstract class Spacecraft implements
    SpacecraftDestroyerInterface,
    EntityWithStorageInterface,
    EntityWithLocationInterface,
    EntityWithCrewAssignmentsInterface,
    EntityWithInteractionCheckInterface
{
    use SpacecraftSystemStateTrait;
    use SpacecraftSystemExistenceTrait;
    use SpacecraftSystemHealthTrait;
    use SpacecraftShieldsTrait;
    use SpacecraftCrewTrait;
    use SpacecraftLocationTrait;
    use SpacecraftStorageTrait;
    use SpacecraftInteractionTrait;
    use SpacecraftHoldingWebTrait;
    use SpacecraftHullColorStyleTrait;
    use SpacecraftTorpedoTrait;
    use SpacecrafCharacteristicsTrait;
    use SpacecraftStateTrait;
    use SpacecraftHrefTrait;

    public const int SYSTEM_ECOST_DOCK = 1;

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

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer', length: 6)]
    private int $max_huelle = 0;

    #[Column(type: 'integer', length: 6)]
    private int $max_schilde = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $tractored_ship_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $holding_web_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $database_id = null;

    #[Column(type: 'integer')]
    private int $location_id = 0;

    #[OneToOne(targetEntity: SpacecraftCondition::class, mappedBy: 'spacecraft', fetch: 'EAGER', cascade: ['all'])]
    private ?SpacecraftCondition $condition;

    #[OneToOne(targetEntity: Ship::class)]
    #[JoinColumn(name: 'tractored_ship_id', referencedColumnName: 'id')]
    private ?Ship $tractoredShip = null;

    #[ManyToOne(targetEntity: TholianWeb::class)]
    #[JoinColumn(name: 'holding_web_id', referencedColumnName: 'id')]
    private ?TholianWeb $holdingWeb = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    /**
     * @var ArrayCollection<int, CrewAssignment>
     */
    #[OneToMany(targetEntity: CrewAssignment::class, mappedBy: 'spacecraft', indexBy: 'crew_id')]
    #[OrderBy(['crew' => 'ASC'])]
    private Collection $crew;

    #[OneToOne(targetEntity: TorpedoStorage::class, mappedBy: 'spacecraft')]
    private ?TorpedoStorage $torpedoStorage = null;

    /**
     * @var ArrayCollection<int, SpacecraftSystem>
     */
    #[OneToMany(targetEntity: SpacecraftSystem::class, mappedBy: 'spacecraft', indexBy: 'system_type')]
    #[OrderBy(['system_type' => 'ASC'])]
    private Collection $systems;

    #[ManyToOne(targetEntity: SpacecraftRump::class)]
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id')]
    private SpacecraftRump $rump;

    #[ManyToOne(targetEntity: SpacecraftBuildplan::class)]
    #[JoinColumn(name: 'plan_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?SpacecraftBuildplan $buildplan = null;

    /**
     * @var ArrayCollection<int, Storage>
     */
    #[OneToMany(targetEntity: Storage::class, mappedBy: 'spacecraft', indexBy: 'commodity_id')]
    #[OrderBy(['commodity_id' => 'ASC'])]
    private Collection $storage;

    #[ManyToOne(targetEntity: Location::class)]
    #[JoinColumn(name: 'location_id', nullable: false, referencedColumnName: 'id')]
    private Location $location;

    /**
     * @var ArrayCollection<int, ShipLog>
     */
    #[OneToMany(targetEntity: ShipLog::class, mappedBy: 'spacecraft', fetch: 'EXTRA_LAZY')]
    #[OrderBy(['id' => 'DESC'])]
    private Collection $logbook;

    #[OneToOne(targetEntity: ShipTakeover::class, mappedBy: 'source')]
    private ?ShipTakeover $takeoverActive = null;

    #[OneToOne(targetEntity: ShipTakeover::class, mappedBy: 'target')]
    private ?ShipTakeover $takeoverPassive = null;

    public function __construct()
    {
        $this->crew = new ArrayCollection();
        $this->systems = new ArrayCollection();
        $this->storage = new ArrayCollection();
        $this->logbook = new ArrayCollection();
    }

    abstract public function getType(): SpacecraftTypeEnum;

    abstract public function getFleet(): ?Fleet;

    public function getId(): int
    {
        if ($this->id === null) {
            throw new BadMethodCallException(sprintf('entity "%s" not yet persisted', $this->getName()));
        }

        return $this->id;
    }

    public function getCondition(): SpacecraftCondition
    {
        return $this->condition ?? throw new LogicException('Spacecraft has no condition');
    }

    public function setCondition(SpacecraftCondition $condition): Spacecraft
    {
        $this->condition = $condition;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUserName(): string
    {
        return $this->getUser()->getName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Spacecraft
    {
        $this->name = $name;
        return $this;
    }

    public function getMaxHull(): int
    {
        return $this->max_huelle;
    }

    public function setMaxHuell(int $maxHull): Spacecraft
    {
        $this->max_huelle = $maxHull;
        return $this;
    }

    public function setMaxShield(int $maxShields): Spacecraft
    {
        $this->max_schilde = $maxShields;
        return $this;
    }

    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    public function setDatabaseId(?int $databaseEntryId): Spacecraft
    {
        $this->database_id = $databaseEntryId;
        return $this;
    }

    public function getCrewAssignments(): Collection
    {
        return $this->crew;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Spacecraft
    {
        $this->user = $user;
        return $this;
    }

    /** @return array<int, Module>*/
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

    public function getTorpedoStorage(): ?TorpedoStorage
    {
        return $this->torpedoStorage;
    }

    public function setTorpedoStorage(?TorpedoStorage $torpedoStorage): Spacecraft
    {
        $this->torpedoStorage = $torpedoStorage;
        return $this;
    }

    public function getStorage(): Collection
    {
        return $this->storage;
    }

    public function getLocation(): Map|StarSystemMap
    {
        if (
            $this->location instanceof Map
            || $this->location instanceof StarSystemMap
        ) {
            return $this->location;
        }

        throw new RuntimeException('unknown type');
    }

    /**
     * @return Collection<int, ShipLog> Ordered by id
     */
    public function getLogbook(): Collection
    {
        return $this->logbook;
    }

    public function getTakeoverActive(): ?ShipTakeover
    {
        return $this->takeoverActive;
    }

    public function setTakeoverActive(?ShipTakeover $takeover): Spacecraft
    {
        $this->takeoverActive = $takeover;

        return $this;
    }

    public function getTakeoverPassive(): ?ShipTakeover
    {
        return $this->takeoverPassive;
    }

    public function setTakeoverPassive(?ShipTakeover $takeover): Spacecraft
    {
        $this->takeoverPassive = $takeover;

        return $this;
    }

    public function setLocation(Location $location): Spacecraft
    {
        $this->location = $location;

        return $this;
    }

    public function getBuildplan(): ?SpacecraftBuildplan
    {
        return $this->buildplan;
    }

    public function setBuildplan(?SpacecraftBuildplan $spacecraftBuildplan): Spacecraft
    {
        $this->buildplan = $spacecraftBuildplan;
        return $this;
    }

    /**
     * @return Collection<int, SpacecraftSystem>
     */
    public function getSystems(): Collection
    {
        return $this->systems;
    }

    public function getTractoredShip(): ?Ship
    {
        return $this->tractoredShip;
    }

    public function setTractoredShip(?Ship $ship): Spacecraft
    {
        $this->tractoredShip = $ship;
        return $this;
    }

    public function getHoldingWeb(): ?TholianWeb
    {
        return $this->holdingWeb;
    }

    public function setHoldingWeb(?TholianWeb $web): Spacecraft
    {
        $this->holdingWeb = $web;

        return $this;
    }

    public function getRump(): SpacecraftRump
    {
        return $this->rump;
    }

    public function getRumpId(): int
    {
        return $this->getRump()->getId();
    }

    public function getRumpName(): string
    {
        return $this->getRump()->getName();
    }

    public function setRump(SpacecraftRump $shipRump): Spacecraft
    {
        $this->rump = $shipRump;
        return $this;
    }

    public function getState(): SpacecraftStateEnum
    {
        return $this->getCondition()->getState();
    }

    public function __toString(): string
    {
        if ($this->id !== null) {
            return sprintf(
                "id: %d, name: %s",
                $this->getId(),
                $this->getName()
            );
        }

        return $this->getName();
    }
}
