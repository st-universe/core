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
use LogicException;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Colony\Trait\ColonyRotationTrait;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\Colony\PlanetFieldHostTypeEnum;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Lib\Map\EntityWithLocationInterface;
use Stu\Lib\Transfer\CommodityTransfer;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Orm\Repository\ColonyRepository;

#[Table(name: 'stu_colony')]
#[Entity(repositoryClass: ColonyRepository::class)]
class Colony implements
    PlanetFieldHostInterface,
    EntityWithStorageInterface,
    EntityWithLocationInterface,
    EntityWithCrewAssignmentsInterface,
    EntityWithInteractionCheckInterface
{
    use ColonyRotationTrait;

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[OneToOne(targetEntity: ColonyChangeable::class, mappedBy: 'colony', fetch: 'EAGER', cascade: ['all'])]
    private ?ColonyChangeable $changeable;

    #[Column(type: 'integer')]
    private int $colonies_classes_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'string', length: 100)]
    private string $planet_name = '';

    #[Column(type: 'text', nullable: true)]
    private ?string $mask = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $database_id = null;

    #[Column(type: 'integer', length: 3)]
    private int $rotation_factor = 1;

    #[Column(type: 'integer', length: 2)]
    private int $surface_width = 0;

    #[ManyToOne(targetEntity: ColonyClass::class)]
    #[JoinColumn(name: 'colonies_classes_id', nullable: false, referencedColumnName: 'id')]
    private ColonyClass $colonyClass;

    #[OneToOne(targetEntity: StarSystemMap::class, inversedBy: 'colony')]
    #[JoinColumn(name: 'starsystem_map_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private StarSystemMap $starsystemMap;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id')]
    private User $user;

    /**
     * @var ArrayCollection<int, PlanetField>
     */
    #[OneToMany(
        targetEntity: PlanetField::class,
        mappedBy: 'colony',
        indexBy: 'field_id',
        fetch: 'EXTRA_LAZY'
    )]
    #[OrderBy(['field_id' => 'ASC'])]
    private Collection $planetFields;

    /**
     * @var ArrayCollection<int, Storage>
     */
    #[OneToMany(targetEntity: Storage::class, mappedBy: 'colony', indexBy: 'commodity_id')]
    #[OrderBy(['commodity_id' => 'ASC'])]
    private Collection $storage;

    #[OneToOne(targetEntity: DatabaseEntry::class)]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntry $databaseEntry;

    /**
     * @var ArrayCollection<int, Fleet>
     */
    #[OneToMany(targetEntity: Fleet::class, mappedBy: 'defendedColony')]
    private Collection $defenders;

    /**
     * @var ArrayCollection<int, Fleet>
     */
    #[OneToMany(targetEntity: Fleet::class, mappedBy: 'blockedColony')]
    private Collection $blockers;

    /**
     * @var ArrayCollection<int, CrewAssignment>
     */
    #[OneToMany(targetEntity: CrewAssignment::class, mappedBy: 'colony')]
    private Collection $crewAssignments;

    /**
     * @var ArrayCollection<int, CrewTraining>
     */
    #[OneToMany(targetEntity: CrewTraining::class, mappedBy: 'colony')]
    private Collection $crewTrainings;

    /** @var array<int, int> */
    private array $twilightZones = [];

    public function __construct()
    {
        $this->planetFields = new ArrayCollection();
        $this->storage = new ArrayCollection();
        $this->defenders = new ArrayCollection();
        $this->blockers = new ArrayCollection();
        $this->crewAssignments = new ArrayCollection();
        $this->crewTrainings = new ArrayCollection();
    }

    #[\Override]
    public function getId(): int
    {
        return $this->id;
    }

    public function getChangeable(): ColonyChangeable
    {
        return $this->changeable ?? throw new LogicException('Colony has no changeable');
    }

    public function setChangeable(ColonyChangeable $changeable): Colony
    {
        $this->changeable = $changeable;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getSx(): int
    {
        return $this->getStarsystemMap()->getSx();
    }

    public function getSy(): int
    {
        return $this->getStarsystemMap()->getSy();
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Colony
    {
        $this->name = $name;
        return $this;
    }

    public function getPlanetName(): string
    {
        return $this->planet_name;
    }

    public function setPlanetName(string $planet_name): Colony
    {
        $this->planet_name = $planet_name;
        return $this;
    }

    public function getMask(): ?string
    {
        return $this->mask;
    }

    public function setMask(?string $mask): Colony
    {
        $this->mask = $mask;
        return $this;
    }

    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    public function setDatabaseEntry(?DatabaseEntry $entry): Colony
    {
        $this->databaseEntry = $entry;
        return $this;
    }

    public function getRotationFactor(): int
    {
        return $this->rotation_factor;
    }

    public function setRotationFactor(int $rotationFactor): Colony
    {
        $this->rotation_factor = $rotationFactor;

        return $this;
    }

    public function getSurfaceWidth(): int
    {
        return $this->surface_width;
    }

    public function setSurfaceWidth(int $surfaceWidth): Colony
    {
        $this->surface_width = $surfaceWidth;
        return $this;
    }

    #[\Override]
    public function getColonyClass(): ColonyClass
    {
        return $this->colonyClass;
    }

    public function setColonyClass(ColonyClass $colonyClass): Colony
    {
        $this->colonyClass = $colonyClass;
        return $this;
    }

    #[\Override]
    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage()->getValues(),
            fn(int $sum, Storage $storage): int => $sum + $storage->getAmount(),
            0
        );
    }

    public function getStarsystemMap(): StarSystemMap
    {
        return $this->starsystemMap;
    }

    #[\Override]
    public function getLocation(): Map|StarSystemMap
    {
        return $this->getStarsystemMap();
    }

    public function setStarsystemMap(StarSystemMap $systemMap): Colony
    {
        $this->starsystemMap = $systemMap;

        return $this;
    }

    public function getSystem(): StarSystem
    {
        return $this->getStarsystemMap()->getSystem();
    }

    public function getBeamFactor(): int
    {
        return 10;
    }

    #[\Override]
    public function getPlanetFields(): Collection
    {
        return $this->planetFields;
    }

    #[\Override]
    public function getBeamableStorage(): Collection
    {
        return CommodityTransfer::excludeNonBeamable($this->storage);
    }

    #[\Override]
    public function getStorage(): Collection
    {
        return $this->storage;
    }

    public function isDefended(): bool
    {
        return !$this->getDefenders()->isEmpty();
    }

    /**
     * @return Collection<int, Fleet>
     */
    public function getDefenders(): Collection
    {
        return $this->defenders;
    }

    public function isBlocked(): bool
    {
        return !$this->getBlockers()->isEmpty();
    }

    /**
     * @return Collection<int, Fleet>
     */
    public function getBlockers(): Collection
    {
        return $this->blockers;
    }

    #[\Override]
    public function getCrewAssignments(): Collection
    {
        return $this->crewAssignments;
    }

    public function getCrewAssignmentAmount(): int
    {
        return $this->crewAssignments->count();
    }

    public function getCrewTrainingAmount(): int
    {
        return $this->crewTrainings->count();
    }

    public function isFree(): bool
    {
        return $this->getUserId() === UserConstants::USER_NOONE;
    }

    #[\Override]
    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Colony
    {
        $this->user = $user;
        return $this;
    }

    #[\Override]
    public function getWorkers(): int
    {
        return $this->getChangeable()->getWorkers();
    }

    public function getWorkless(): int
    {
        return $this->getChangeable()->getWorkless();
    }

    public function getMaxBev(): int
    {
        return $this->getChangeable()->getMaxBev();
    }

    #[\Override]
    public function getMaxEps(): int
    {
        return $this->getChangeable()->getMaxEps();
    }

    #[\Override]
    public function getMaxStorage(): int
    {
        return $this->getChangeable()->getMaxStorage();
    }

    #[\Override]
    public function getPopulation(): int
    {
        return $this->getChangeable()->getPopulation();
    }

    public function getSectorString(): string
    {
        return $this->getStarsystemMap()->getSectorString();
    }

    #[\Override]
    public function isColony(): bool
    {
        return true;
    }

    #[\Override]
    public function getHostType(): PlanetFieldHostTypeEnum
    {
        return PlanetFieldHostTypeEnum::COLONY;
    }

    #[\Override]
    public function getDefaultViewIdentifier(): string
    {
        return ShowColony::VIEW_IDENTIFIER;
    }

    #[\Override]
    public function isMenuAllowed(ColonyMenuEnum $menu): bool
    {
        return true;
    }

    #[\Override]
    public function getTransferEntityType(): TransferEntityTypeEnum
    {
        return TransferEntityTypeEnum::COLONY;
    }

    #[\Override]
    public function getHref(): string
    {
        return sprintf(
            '%s?%s=1&id=%d',
            ModuleEnum::COLONY->getPhpPage(),
            ShowColony::VIEW_IDENTIFIER,
            $this->getId()
        );
    }

    #[\Override]
    public function getComponentParameters(): string
    {
        return sprintf(
            '&hosttype=%d&id=%d',
            $this->getHostType()->value,
            $this->getId()
        );
    }
}
