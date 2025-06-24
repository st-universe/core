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
use Override;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Colony\Trait\ColonyRotationTrait;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Colony\PlanetFieldHostTypeEnum;
use Stu\Lib\Transfer\CommodityTransfer;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\ColonyRepository;

#[Table(name: 'stu_colony')]
#[Entity(repositoryClass: ColonyRepository::class)]
class Colony implements ColonyInterface
{
    use ColonyRotationTrait;

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[OneToOne(targetEntity: 'ColonyChangeable', mappedBy: 'colony', fetch: 'EAGER', cascade: ['all'])]
    private ColonyChangeableInterface $changeable;

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

    #[ManyToOne(targetEntity: 'ColonyClass')]
    #[JoinColumn(name: 'colonies_classes_id', referencedColumnName: 'id')]
    private ColonyClassInterface $colonyClass;

    #[OneToOne(targetEntity: 'StarSystemMap', inversedBy: 'colony')]
    #[JoinColumn(name: 'starsystem_map_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private StarSystemMapInterface $starsystem_map;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private UserInterface $user;

    /**
     * @var ArrayCollection<int, PlanetFieldInterface>
     */
    #[OneToMany(targetEntity: 'PlanetField', mappedBy: 'colony', indexBy: 'field_id', fetch: 'EXTRA_LAZY')]
    #[OrderBy(['field_id' => 'ASC'])]
    private Collection $planetFields;

    /**
     * @var ArrayCollection<int, StorageInterface>
     */
    #[OneToMany(targetEntity: 'Storage', mappedBy: 'colony', indexBy: 'commodity_id')]
    #[OrderBy(['commodity_id' => 'ASC'])]
    private Collection $storage;

    #[OneToOne(targetEntity: 'DatabaseEntry')]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntryInterface $databaseEntry;

    /**
     * @var ArrayCollection<int, FleetInterface>
     */
    #[OneToMany(targetEntity: 'Fleet', mappedBy: 'defendedColony')]
    private Collection $defenders;

    /**
     * @var ArrayCollection<int, FleetInterface>
     */
    #[OneToMany(targetEntity: 'Fleet', mappedBy: 'blockedColony')]
    private Collection $blockers;

    /**
     * @var ArrayCollection<int, CrewAssignmentInterface>
     */
    #[OneToMany(targetEntity: 'CrewAssignment', mappedBy: 'colony')]
    private Collection $crewAssignments;

    /**
     * @var ArrayCollection<int, CrewAssignmentInterface>
     */
    #[OneToMany(targetEntity: 'CrewTraining', mappedBy: 'colony')]
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

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getChangeable(): ColonyChangeableInterface
    {
        return $this->changeable;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getSx(): int
    {
        return $this->getStarsystemMap()->getSx();
    }

    #[Override]
    public function getSy(): int
    {
        return $this->getStarsystemMap()->getSy();
    }

    #[Override]
    public function getSystemsId(): int
    {
        return $this->getSystem()->getId();
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getNameAndSectorString(): string
    {
        return sprintf(
            '%s %s',
            $this->getName(),
            $this->getSectorString()
        );
    }

    #[Override]
    public function getSystemString(): string
    {
        return sprintf('%s-System (%s|%s)', $this->getSystem()->getName(), $this->getSystem()->getCx(), $this->getSystem()->getCy());
    }

    #[Override]
    public function setName(string $name): ColonyInterface
    {
        $this->name = $name;
        return $this;
    }

    #[Override]
    public function getPlanetName(): string
    {
        return $this->planet_name;
    }

    #[Override]
    public function setPlanetName(string $planet_name): ColonyInterface
    {
        $this->planet_name = $planet_name;
        return $this;
    }

    #[Override]
    public function getMask(): ?string
    {
        return $this->mask;
    }

    #[Override]
    public function setMask(?string $mask): ColonyInterface
    {
        $this->mask = $mask;
        return $this;
    }

    #[Override]
    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    #[Override]
    public function setDatabaseEntry(?DatabaseEntryInterface $entry): ColonyInterface
    {
        $this->databaseEntry = $entry;
        return $this;
    }

    #[Override]
    public function getRotationFactor(): int
    {
        return $this->rotation_factor;
    }

    #[Override]
    public function setRotationFactor(int $rotationFactor): ColonyInterface
    {
        $this->rotation_factor = $rotationFactor;

        return $this;
    }

    #[Override]
    public function getSurfaceWidth(): int
    {
        return $this->surface_width;
    }

    #[Override]
    public function setSurfaceWidth(int $surfaceWidth): ColonyInterface
    {
        $this->surface_width = $surfaceWidth;
        return $this;
    }

    #[Override]
    public function getColonyClass(): ColonyClassInterface
    {
        return $this->colonyClass;
    }

    #[Override]
    public function setColonyClass(ColonyClassInterface $colonyClass): ColonyInterface
    {
        $this->colonyClass = $colonyClass;
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
    public function storagePlaceLeft(): bool
    {
        return $this->getMaxStorage() > $this->getStorageSum();
    }

    #[Override]
    public function getStarsystemMap(): StarSystemMapInterface
    {
        return $this->starsystem_map;
    }

    #[Override]
    public function getLocation(): MapInterface|StarSystemMapInterface
    {
        return $this->getStarsystemMap();
    }

    #[Override]
    public function setStarsystemMap(StarSystemMapInterface $systemMap): ColonyInterface
    {
        $this->starsystem_map = $systemMap;

        return $this;
    }

    #[Override]
    public function getSystem(): StarSystemInterface
    {
        return $this->getStarsystemMap()->getSystem();
    }

    #[Override]
    public function getBeamFactor(): int
    {
        return 10;
    }

    #[Override]
    public function getPlanetFields(): Collection
    {
        return $this->planetFields;
    }

    #[Override]
    public function getBeamableStorage(): Collection
    {
        return CommodityTransfer::excludeNonBeamable($this->storage);
    }

    #[Override]
    public function getStorage(): Collection
    {
        return $this->storage;
    }

    #[Override]
    public function isDefended(): bool
    {
        return !$this->getDefenders()->isEmpty();
    }

    #[Override]
    public function getDefenders(): Collection
    {
        return $this->defenders;
    }

    #[Override]
    public function isBlocked(): bool
    {
        return !$this->getBlockers()->isEmpty();
    }

    #[Override]
    public function getBlockers(): Collection
    {
        return $this->blockers;
    }

    #[Override]
    public function getCrewAssignments(): Collection
    {
        return $this->crewAssignments;
    }

    #[Override]
    public function getCrewAssignmentAmount(): int
    {
        return $this->crewAssignments->count();
    }

    #[Override]
    public function getCrewTrainingAmount(): int
    {
        return $this->crewTrainings->count();
    }

    #[Override]
    public function isFree(): bool
    {
        return $this->getUserId() === UserEnum::USER_NOONE;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): ColonyInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getWorkers(): int
    {
        return $this->changeable->getWorkers();
    }

    public function getWorkless(): int
    {
        return $this->changeable->getWorkless();
    }

    public function getMaxBev(): int
    {
        return $this->changeable->getMaxBev();
    }

    #[Override]
    public function getMaxEps(): int
    {
        return $this->changeable->getMaxEps();
    }

    #[Override]
    public function getMaxStorage(): int
    {
        return $this->changeable->getMaxStorage();
    }

    #[Override]
    public function getPopulation(): int
    {
        return $this->changeable->getPopulation();
    }

    #[Override]
    public function getSectorString(): string
    {
        return $this->getStarsystemMap()->getSectorString();
    }

    #[Override]
    public function getPlanetFieldHostIdentifier(): string
    {
        return 'colony';
    }

    #[Override]
    public function getPlanetFieldHostColumnIdentifier(): string
    {
        return 'colonies_id';
    }

    #[Override]
    public function isColony(): bool
    {
        return true;
    }

    #[Override]
    public function getHostType(): PlanetFieldHostTypeEnum
    {
        return PlanetFieldHostTypeEnum::COLONY;
    }

    #[Override]
    public function getDefaultViewIdentifier(): string
    {
        return ShowColony::VIEW_IDENTIFIER;
    }

    #[Override]
    public function isMenuAllowed(ColonyMenuEnum $menu): bool
    {
        return true;
    }

    #[Override]
    public function getTransferEntityType(): TransferEntityTypeEnum
    {
        return TransferEntityTypeEnum::COLONY;
    }

    #[Override]
    public function getHref(): string
    {
        return sprintf(
            '%s?%s=1&id=%d',
            ModuleEnum::COLONY->getPhpPage(),
            ShowColony::VIEW_IDENTIFIER,
            $this->getId()
        );
    }

    #[Override]
    public function getComponentParameters(): string
    {
        return sprintf(
            '&hosttype=%d&id=%d',
            $this->getHostType()->value,
            $this->getId()
        );
    }
}
