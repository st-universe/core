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
use Stu\Component\Game\TimeConstants;
use Stu\Module\PlayerSetting\Lib\UserEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ColonyRepository")
 * @Table(
 *     name="stu_colonies",
 *     indexes={
 *         @Index(name="colony_user_idx", columns={"user_id"}),
 *         @Index(name="colony_classes_idx", columns={"colonies_classes_id"}),
 *         @Index(name="colony_sys_map_idx", columns={"starsystem_map_id"})
 *     }
 * )
 **/
class Colony implements ColonyInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $colonies_classes_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $user_id = 0;

    /**
     * @Column(type="integer", nullable=true) *
     *
     */
    private ?int $starsystem_map_id = null;

    /**
     * @Column(type="string")
     *
     */
    private string $name = '';

    /**
     * @Column(type="string", length=100)
     *
     */
    private string $planet_name = '';

    /**
     * @Column(type="integer", length=5)
     *
     */
    private int $bev_work = 0;

    /**
     * @Column(type="integer", length=5)
     *
     */
    private int $bev_free = 0;

    /**
     * @Column(type="integer", length=5)
     *
     */
    private int $bev_max = 0;

    /**
     * @Column(type="integer", length=5)
     *
     */
    private int $eps = 0;

    /**
     * @Column(type="integer", length=5)
     *
     */
    private int $max_eps = 0;

    /**
     * @Column(type="integer", length=5)
     *
     */
    private int $max_storage = 0;

    /**
     * @Column(type="text", nullable=true)
     *
     */
    private ?string $mask = null;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $database_id = null;

    /**
     * @Column(type="integer", length=5)
     *
     */
    private int $populationlimit = 0;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $immigrationstate = true;

    /**
     * @Column(type="integer", length=6, nullable=true)
     *
     */
    private ?int $shields = 0;

    /**
     * @Column(type="integer", length=6, nullable=true)
     *
     */
    private ?int $shield_frequency = 0;

    /**
     * @Column(type="integer", length=3, nullable=true)
     *
     */
    private ?int $torpedo_type = null;

    /**
     * @Column(type="integer", length=3)
     *
     */
    private int $rotation_factor = 1;

    /**
     * @Column(type="integer", length=2)
     *
     */
    private int $surface_width = 0;

    /**
     *
     * @ManyToOne(targetEntity="ColonyClass")
     * @JoinColumn(name="colonies_classes_id", referencedColumnName="id")
     */
    private ColonyClassInterface $colonyClass;

    /**
     *
     * @OneToOne(targetEntity="StarSystemMap", inversedBy="colony")
     * @JoinColumn(name="starsystem_map_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?StarSystemMapInterface $starsystem_map = null;

    /**
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private UserInterface $user;

    /**
     * @var ArrayCollection<int, PlanetFieldInterface>
     *
     * @OneToMany(targetEntity="PlanetField", mappedBy="colony", indexBy="field_id", fetch="EXTRA_LAZY")
     * @OrderBy({"field_id": "ASC"})
     */
    private Collection $planetFields;

    /**
     * @var ArrayCollection<int, StorageInterface>
     *
     * @OneToMany(targetEntity="Storage", mappedBy="colony", indexBy="commodity_id")
     * @OrderBy({"commodity_id": "ASC"})
     */
    private Collection $storage;

    /**
     *
     * @ManyToOne(targetEntity="TorpedoType")
     * @JoinColumn(name="torpedo_type", referencedColumnName="id")
     */
    private ?TorpedoTypeInterface $torpedo = null;

    /**
     * @var ArrayCollection<int, FleetInterface>
     *
     * @OneToMany(targetEntity="Fleet", mappedBy="defendedColony")
     */
    private Collection $defenders;

    /**
     * @var ArrayCollection<int, FleetInterface>
     *
     * @OneToMany(targetEntity="Fleet", mappedBy="blockedColony")
     */
    private Collection $blockers;

    /**
     * @var ArrayCollection<int, ShipCrewInterface>
     *
     * @OneToMany(targetEntity="ShipCrew", mappedBy="colony")
     */
    private Collection $crewAssignments;

    /**
     * @var ArrayCollection<int, ShipCrewInterface>
     *
     * @OneToMany(targetEntity="CrewTraining", mappedBy="colony")
     */
    private Collection $crewTrainings;

    /**
     * @var Collection<int, ColonyDepositMiningInterface>
     *
     * @OneToMany(targetEntity="ColonyDepositMining", mappedBy="colony")
     * @OrderBy({"commodity_id": "ASC"})
     */
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getColonyClassId(): int
    {
        return $this->colonies_classes_id;
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

    public function getSystemsId(): int
    {
        return $this->getSystem()->getId();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameAndSectorString(): string
    {
        return sprintf(
            '%s %s',
            $this->getName(),
            $this->getSectorString()
        );
    }

    public function setName(string $name): ColonyInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getPlanetName(): string
    {
        return $this->planet_name;
    }

    public function setPlanetName(string $planet_name): ColonyInterface
    {
        $this->planet_name = $planet_name;
        return $this;
    }

    public function getWorkers(): int
    {
        return $this->bev_work;
    }

    public function setWorkers(int $bev_work): ColonyInterface
    {
        $this->bev_work = $bev_work;
        return $this;
    }

    public function getWorkless(): int
    {
        return $this->bev_free;
    }

    public function setWorkless(int $bev_free): ColonyInterface
    {
        $this->bev_free = $bev_free;
        return $this;
    }

    public function getMaxBev(): int
    {
        return $this->bev_max;
    }

    public function setMaxBev(int $bev_max): ColonyInterface
    {
        $this->bev_max = $bev_max;
        return $this;
    }

    public function getEps(): int
    {
        return $this->eps;
    }

    public function setEps(int $eps): ColonyInterface
    {
        $this->eps = $eps;
        return $this;
    }

    public function getMaxEps(): int
    {
        return $this->max_eps;
    }

    public function setMaxEps(int $max_eps): ColonyInterface
    {
        $this->max_eps = $max_eps;
        return $this;
    }

    public function getMaxStorage(): int
    {
        return $this->max_storage;
    }

    public function setMaxStorage(int $max_storage): ColonyInterface
    {
        $this->max_storage = $max_storage;
        return $this;
    }

    public function getMask(): ?string
    {
        return $this->mask;
    }

    public function setMask(?string $mask): ColonyInterface
    {
        $this->mask = $mask;
        return $this;
    }

    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    public function setDatabaseId(?int $database_id): ColonyInterface
    {
        $this->database_id = $database_id;
        return $this;
    }

    public function getPopulationlimit(): int
    {
        return $this->populationlimit;
    }

    public function setPopulationlimit(int $populationlimit): ColonyInterface
    {
        $this->populationlimit = $populationlimit;
        return $this;
    }

    public function getImmigrationstate(): bool
    {
        return $this->immigrationstate;
    }

    public function setImmigrationstate(bool $immigrationstate): ColonyInterface
    {
        $this->immigrationstate = $immigrationstate;
        return $this;
    }

    public function getShields(): ?int
    {
        return $this->shields;
    }

    public function setShields(?int $shields): ColonyInterface
    {
        $this->shields = $shields;
        return $this;
    }

    public function getTwilightZone(): int
    {
        $twilightZone = 0;

        $width = $this->getSurfaceWidth();
        $rotationTime = $this->getRotationTime();
        $colonyTimeSeconds = $this->getColonyTimeSeconds();

        if ($this->getDayTimePrefix() == 1) {
            $scaled = floor((((100 / ($rotationTime * 0.125)) * ($colonyTimeSeconds - $rotationTime * 0.25)) / 100) * $width);
            if ($scaled == 0) {
                $twilightZone = - (($width) - 1);
            } elseif ((int) - (($width) - ceil($scaled)) == 0) {
                $twilightZone = -1;
            } else {
                $twilightZone = (int) - (($width) - $scaled);
            }
        }
        if ($this->getDayTimePrefix() == 2) {
            $twilightZone = $width;
        }
        if ($this->getDayTimePrefix() == 3) {
            $scaled = floor((((100 / ($rotationTime * 0.125)) * ($colonyTimeSeconds - $rotationTime * 0.75)) / 100) * $width);
            $twilightZone = (int) ($width - $scaled);
        }
        if ($this->getDayTimePrefix() == 4) {
            $twilightZone = 0;
        }

        return $twilightZone;
    }

    public function getShieldFrequency(): ?int
    {
        return $this->shield_frequency;
    }

    public function setShieldFrequency(?int $shieldFrequency): ColonyInterface
    {
        $this->shield_frequency = $shieldFrequency;
        return $this;
    }

    public function getTorpedo(): ?TorpedoTypeInterface
    {
        return $this->torpedo;
    }

    public function setTorpedo(?TorpedoTypeInterface $torpedoType): ColonyInterface
    {
        $this->torpedo = $torpedoType;
        return $this;
    }

    public function getRotationFactor(): int
    {
        return $this->rotation_factor;
    }

    public function setRotationFactor(int $rotationFactor): ColonyInterface
    {
        $this->rotation_factor = $rotationFactor;

        return $this;
    }

    public function getRotationTime(): int
    {
        return (int) (TimeConstants::ONE_DAY_IN_SECONDS * $this->getRotationFactor() / 100);
    }

    public function getColonyTimeSeconds(): int
    {
        return time() % $this->getRotationTime();
    }

    public function getColonyTimeHour(): ?string
    {
        $rotationTime = $this->getRotationTime();

        return sprintf("%02d", (int) floor(($rotationTime / 3600) * ($this->getColonyTimeSeconds() / $rotationTime)));
    }

    public function getColonyTimeMinute(): ?string
    {
        $rotationTime = $this->getRotationTime();

        return sprintf("%02d", (int) floor(60 * (($rotationTime / 3600) * ($this->getColonyTimeSeconds() / $rotationTime) - ((int) $this->getColonyTimeHour()))));
    }

    public function getDayTimePrefix(): ?int
    {
        $daytimeprefix = null;
        $daypercent = (int) (($this->getColonyTimeSeconds() / $this->getRotationTime()) * 100);
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

    public function getDayTimeName(): ?string
    {
        $daytimename = null;
        if ($this->getDayTimePrefix() == 1) {
            $daytimename = 'Morgen';
        }

        if ($this->getDayTimePrefix() == 2) {
            $daytimename = 'Tag';
        }

        if ($this->getDayTimePrefix() == 3) {
            $daytimename = 'Abend';
        }

        if ($this->getDayTimePrefix() == 4) {
            $daytimename = 'Nacht';
        }
        return $daytimename;
    }

    public function getSurfaceWidth(): int
    {
        return $this->surface_width;
    }

    public function setSurfaceWidth(int $surfaceWidth): ColonyInterface
    {
        $this->surface_width = $surfaceWidth;
        return $this;
    }

    public function getColonyClass(): ColonyClassInterface
    {
        return $this->colonyClass;
    }

    public function setColonyClass(ColonyClassInterface $colonyClass): ColonyInterface
    {
        $this->colonyClass = $colonyClass;
        return $this;
    }

    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage()->getValues(),
            fn (int $sum, StorageInterface $storage): int => $sum + $storage->getAmount(),
            0
        );
    }

    public function storagePlaceLeft(): bool
    {
        return $this->getMaxStorage() > $this->getStorageSum();
    }

    public function isInSystem(): bool
    {
        return $this->getStarsystemMap() !== null;
    }

    public function getStarsystemMap(): ?StarSystemMapInterface
    {
        return $this->starsystem_map;
    }

    public function setStarsystemMap(StarSystemMapInterface $systemMap): ColonyInterface
    {
        $this->starsystem_map = $systemMap;

        return $this;
    }

    public function getSystem(): StarSystemInterface
    {
        return $this->getStarsystemMap()->getSystem();
    }

    public function getBeamFactor(): int
    {
        return 10;
    }

    public function getPlanetFields(): Collection
    {
        return $this->planetFields;
    }

    /**
     * @return StorageInterface[]
     */
    public function getBeamableStorage(): array
    {
        return array_filter(
            $this->getStorage()->getValues(),
            fn (StorageInterface $storage): bool => $storage->getCommodity()->isBeamable() === true
        );
    }

    public function getStorage(): Collection
    {
        return $this->storage;
    }

    public function isDefended(): bool
    {
        return !$this->getDefenders()->isEmpty();
    }

    public function getDefenders(): Collection
    {
        return $this->defenders;
    }

    public function isBlocked(): bool
    {
        return !$this->getBlockers()->isEmpty();
    }

    public function getBlockers(): Collection
    {
        return $this->blockers;
    }

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

    public function isFree(): bool
    {
        return $this->getUserId() === UserEnum::USER_NOONE;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): ColonyInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getPopulation(): int
    {
        return $this->getWorkers() + $this->getWorkless();
    }

    public function getFreeHousing(): int
    {
        return $this->getMaxBev() - $this->getPopulation();
    }

    public function lowerEps(int $value): void
    {
        $this->setEps($this->getEps() - $value);
    }

    public function upperEps(int $value): void
    {
        $this->setEps($this->getEps() + $value);
    }

    public function getSectorString(): string
    {
        return $this->getStarsystemMap()->getSectorString();
    }

    public function getDepositMinings(): Collection
    {
        return $this->depositMinings;
    }
}
