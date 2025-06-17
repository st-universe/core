<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Lib\Colony\PlanetFieldHostTypeEnum;
use Stu\Lib\Transfer\CommodityTransfer;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\ColonyRepository;

#[Table(name: 'stu_colonies')]
#[Index(name: 'colony_user_idx', columns: ['user_id'])]
#[Index(name: 'colony_classes_idx', columns: ['colonies_classes_id'])]
#[Index(name: 'colony_sys_map_idx', columns: ['starsystem_map_id'])]
#[Entity(repositoryClass: ColonyRepository::class)]
class Colony implements ColonyInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $colonies_classes_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $starsystem_map_id = 0;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'string', length: 100)]
    private string $planet_name = '';

    #[Column(type: 'integer', length: 5)]
    private int $bev_work = 0;

    #[Column(type: 'integer', length: 5)]
    private int $bev_free = 0;

    #[Column(type: 'integer', length: 5)]
    private int $bev_max = 0;

    #[Column(type: 'integer', length: 5)]
    private int $eps = 0;

    #[Column(type: 'integer', length: 5)]
    private int $max_eps = 0;

    #[Column(type: 'integer', length: 5)]
    private int $max_storage = 0;

    #[Column(type: 'text', nullable: true)]
    private ?string $mask = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $database_id = null;

    #[Column(type: 'integer', length: 5)]
    private int $populationlimit = 0;

    #[Column(type: 'boolean')]
    private bool $immigrationstate = true;

    #[Column(type: 'integer', length: 6, nullable: true)]
    private ?int $shields = 0;

    #[Column(type: 'integer', length: 6, nullable: true)]
    private ?int $shield_frequency = 0;

    #[Column(type: 'integer', length: 3, nullable: true)]
    private ?int $torpedo_type = null;

    #[Column(type: 'integer', length: 3)]
    private int $rotation_factor = 1;

    #[Column(type: 'integer', length: 2)]
    private int $surface_width = 0;

    #[ManyToOne(targetEntity: 'ColonyClass')]
    #[JoinColumn(name: 'colonies_classes_id', referencedColumnName: 'id')]
    private ColonyClassInterface $colonyClass;

    #[OneToOne(targetEntity: 'StarSystemMap', inversedBy: 'colony')]
    #[JoinColumn(name: 'starsystem_map_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
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

    #[ManyToOne(targetEntity: 'TorpedoType')]
    #[JoinColumn(name: 'torpedo_type', referencedColumnName: 'id')]
    private ?TorpedoTypeInterface $torpedo = null;

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

    /**
     * @var ArrayCollection<int, ColonyDepositMiningInterface>
     */
    #[OneToMany(targetEntity: 'ColonyDepositMining', mappedBy: 'colony')]
    #[OrderBy(['commodity_id' => 'ASC'])]
    private Collection $depositMinings;

    public function __construct()
    {
        $this->planetFields = new ArrayCollection();
        $this->storage = new ArrayCollection();
        $this->defenders = new ArrayCollection();
        $this->blockers = new ArrayCollection();
        $this->crewAssignments = new ArrayCollection();
        $this->crewTrainings = new ArrayCollection();
        $this->depositMinings = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getColonyClassId(): int
    {
        return $this->colonies_classes_id;
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
    public function getWorkers(): int
    {
        return $this->bev_work;
    }

    #[Override]
    public function setWorkers(int $bev_work): ColonyInterface
    {
        $this->bev_work = $bev_work;
        return $this;
    }

    #[Override]
    public function getWorkless(): int
    {
        return $this->bev_free;
    }

    #[Override]
    public function setWorkless(int $bev_free): ColonyInterface
    {
        $this->bev_free = $bev_free;
        return $this;
    }

    #[Override]
    public function getMaxBev(): int
    {
        return $this->bev_max;
    }

    #[Override]
    public function setMaxBev(int $bev_max): ColonyInterface
    {
        $this->bev_max = $bev_max;
        return $this;
    }

    #[Override]
    public function getEps(): int
    {
        return $this->eps;
    }

    #[Override]
    public function setEps(int $eps): ColonyInterface
    {
        $this->eps = $eps;
        return $this;
    }

    #[Override]
    public function getMaxEps(): int
    {
        return $this->max_eps;
    }

    #[Override]
    public function setMaxEps(int $max_eps): ColonyInterface
    {
        $this->max_eps = $max_eps;
        return $this;
    }

    #[Override]
    public function getMaxStorage(): int
    {
        return $this->max_storage;
    }

    #[Override]
    public function setMaxStorage(int $max_storage): ColonyInterface
    {
        $this->max_storage = $max_storage;
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
    public function getPopulationlimit(): int
    {
        return $this->populationlimit;
    }

    #[Override]
    public function setPopulationlimit(int $populationlimit): ColonyInterface
    {
        $this->populationlimit = $populationlimit;
        return $this;
    }

    #[Override]
    public function getImmigrationstate(): bool
    {
        return $this->immigrationstate;
    }

    #[Override]
    public function setImmigrationstate(bool $immigrationstate): ColonyInterface
    {
        $this->immigrationstate = $immigrationstate;
        return $this;
    }

    #[Override]
    public function getShields(): ?int
    {
        return $this->shields;
    }

    #[Override]
    public function setShields(?int $shields): ColonyInterface
    {
        $this->shields = $shields;
        return $this;
    }

    #[Override]
    public function getTwilightZone(int $timestamp): int
    {
        $twilightZone = 0;

        $width = $this->getSurfaceWidth();
        $rotationTime = $this->getRotationTime();
        $colonyTimeSeconds = $this->getColonyTimeSeconds($timestamp);

        if ($this->getDayTimePrefix($timestamp) == 1) {
            $scaled = floor((((100 / ($rotationTime * 0.125)) * ($colonyTimeSeconds - $rotationTime * 0.25)) / 100) * $width);
            if ($scaled == 0) {
                $twilightZone = - (($width) - 1);
            } elseif ((int) - (($width) - ceil($scaled)) == 0) {
                $twilightZone = -1;
            } else {
                $twilightZone = (int) - (($width) - $scaled);
            }
        }
        if ($this->getDayTimePrefix($timestamp) == 2) {
            $twilightZone = $width;
        }
        if ($this->getDayTimePrefix($timestamp) == 3) {
            $scaled = floor((((100 / ($rotationTime * 0.125)) * ($colonyTimeSeconds - $rotationTime * 0.75)) / 100) * $width);
            $twilightZone = (int) ($width - $scaled);
        }
        if ($this->getDayTimePrefix($timestamp) == 4) {
            $twilightZone = 0;
        }

        return $twilightZone;
    }

    #[Override]
    public function getShieldFrequency(): ?int
    {
        return $this->shield_frequency;
    }

    #[Override]
    public function setShieldFrequency(?int $shieldFrequency): ColonyInterface
    {
        $this->shield_frequency = $shieldFrequency;
        return $this;
    }

    #[Override]
    public function getTorpedo(): ?TorpedoTypeInterface
    {
        return $this->torpedo;
    }

    #[Override]
    public function setTorpedo(?TorpedoTypeInterface $torpedoType): ColonyInterface
    {
        $this->torpedo = $torpedoType;
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
    public function getRotationTime(): int
    {
        return (int) (TimeConstants::ONE_DAY_IN_SECONDS * $this->getRotationFactor() / 100);
    }

    public function getColonyTimeSeconds(int $timestamp): int
    {
        return $timestamp % $this->getRotationTime();
    }

    #[Override]
    public function getColonyTimeHour(int $timestamp): ?string
    {
        $rotationTime = $this->getRotationTime();

        return sprintf("%02d", (int) floor(($rotationTime / 3600) * ($this->getColonyTimeSeconds($timestamp) / $rotationTime)));
    }

    #[Override]
    public function getColonyTimeMinute(int $timestamp): ?string
    {
        $rotationTime = $this->getRotationTime();

        return sprintf("%02d", (int) floor(60 * (($rotationTime / 3600) * ($this->getColonyTimeSeconds($timestamp) / $rotationTime) - ((int) $this->getColonyTimeHour($timestamp)))));
    }

    #[Override]
    public function getDayTimePrefix(int $timestamp): ?int
    {
        $daytimeprefix = null;
        $daypercent = (int) (($this->getColonyTimeSeconds($timestamp) / $this->getRotationTime()) * 100);
        if ($daypercent > 25 && $daypercent <= 37.5) {
            $daytimeprefix = 1; //Sonnenaufgang
        }
        if ($daypercent > 37.5 && $daypercent <= 75) {
            $daytimeprefix = 2; //Tag
        }
        if ($daypercent > 75 && $daypercent <= 87.5) {
            $daytimeprefix = 3; //Sonnenuntergang
        }
        if ($daypercent > 87.5 || $daypercent <= 25) {
            $daytimeprefix = 4; //Nacht
        }
        return $daytimeprefix;
    }

    #[Override]
    public function getDayTimeName(int $timestamp): ?string
    {
        $daytimename = null;
        if ($this->getDayTimePrefix($timestamp) == 1) {
            $daytimename = 'Morgen';
        }

        if ($this->getDayTimePrefix($timestamp) == 2) {
            $daytimename = 'Tag';
        }

        if ($this->getDayTimePrefix($timestamp) == 3) {
            $daytimename = 'Abend';
        }

        if ($this->getDayTimePrefix($timestamp) == 4) {
            $daytimename = 'Nacht';
        }
        return $daytimename;
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
    public function getUserDepositMinings(): array
    {
        $result = [];

        foreach ($this->depositMinings as $deposit) {
            if ($deposit->getUser() === $this->getUser()) {
                $result[$deposit->getCommodity()->getId()] = $deposit;
            }
        }

        return $result;
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
    public function getPopulation(): int
    {
        return $this->getWorkers() + $this->getWorkless();
    }

    #[Override]
    public function getFreeHousing(): int
    {
        return $this->getMaxBev() - $this->getPopulation();
    }

    #[Override]
    public function lowerEps(int $value): void
    {
        $this->setEps($this->getEps() - $value);
    }

    #[Override]
    public function upperEps(int $value): void
    {
        $this->setEps($this->getEps() + $value);
    }

    #[Override]
    public function getSectorString(): string
    {
        return $this->getStarsystemMap()->getSectorString();
    }

    #[Override]
    public function getDepositMinings(): Collection
    {
        return $this->depositMinings;
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
