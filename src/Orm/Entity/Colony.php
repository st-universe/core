<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Component\Faction\FactionEnum;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Tick\Colony\ColonyTick;
use Stu\Orm\Repository\BuildingGoodRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ColonyRepository")
 * @Table(
 *     name="stu_colonies",
 *     indexes={
 *         @Index(name="colony_user_idx", columns={"user_id"}),
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
     */
    private $id;

    /** @Column(type="integer") */
    private $colonies_classes_id = 0;

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private $starsystem_map_id;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="string", length=100) */
    private $planet_name = '';

    /** @Column(type="integer", length=5) */
    private $bev_work = 0;

    /** @Column(type="integer", length=5) */
    private $bev_free = 0;

    /** @Column(type="integer", length=5) */
    private $bev_max = 0;

    /** @Column(type="integer", length=5) */
    private $eps = 0;

    /** @Column(type="integer", length=5) */
    private $max_eps = 0;

    /** @Column(type="integer", length=5) */
    private $max_storage = 0;

    /** @Column(type="text", nullable=true) */
    private $mask;

    /** @Column(type="integer", nullable=true) */
    private $database_id;

    /** @Column(type="integer", length=5) */
    private $populationlimit = 0;

    /** @Column(type="boolean") */
    private $immigrationstate = true;

    /** @Column(type="integer", length=6, nullable=true) */
    private $shields = 0;

    /** @Column(type="integer", length=6, nullable=true) */
    private $shield_frequency = 0;

    /** @Column(type="integer", length=3, nullable=true) */
    private $torpedo_type;

    /** @Column(type="integer", length=3, nullable=true) */
    private $rotation_factor = 1;

    /** @Column(type="integer", length=2, nullable=true) */
    private $surface_width = 1;

    /**
     * @ManyToOne(targetEntity="PlanetType")
     * @JoinColumn(name="colonies_classes_id", referencedColumnName="id")
     */
    private $planetType;

    /**
     * @OneToOne(targetEntity="StarSystemMap", inversedBy="colony")
     * @JoinColumn(name="starsystem_map_id", referencedColumnName="id")
     */
    private $starsystem_map;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @OneToMany(targetEntity="PlanetField", mappedBy="colony", indexBy="field_id", fetch="EXTRA_LAZY")
     * @OrderBy({"field_id" = "ASC"})
     */
    private $planetFields;

    /**
     * @OneToMany(targetEntity="ColonyStorage", mappedBy="colony", indexBy="goods_id")
     * @OrderBy({"goods_id" = "ASC"})
     */
    private $storage;

    /**
     * @ManyToOne(targetEntity="TorpedoType")
     * @JoinColumn(name="torpedo_type", referencedColumnName="id")
     */
    private $torpedo;

    /**
     * @OneToMany(targetEntity="Fleet", mappedBy="defendedColony")
     */
    private $defenders;

    /**
     * @OneToMany(targetEntity="Fleet", mappedBy="blockedColony")
     */
    private $blockers;

    private $has_active_building_by_function = [];

    private $positive_effect_secondary;

    private $positive_effect_primary;

    private $productionRaw;

    private $production;

    private $productionsum;

    private $shiplist;

    private $maxShields;

    private $twilightZone;

    public function __construct()
    {
        $this->planetFields = new ArrayCollection();
        $this->storage = new ArrayCollection();
        $this->defenders = new ArrayCollection();
        $this->blockers = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getColonyClass(): int
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

    public function setDatabaseId(?int $database_id)
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

    public function getMaxShields(): int
    {
        if (!isset($this->maxShields)) {
            // @todo refactor
            global $container;

            $this->maxShields = $container
                ->get(PlanetFieldRepositoryInterface::class)
                ->getMaxShieldsOfColony($this->getId());
        }
        return $this->maxShields;
    }

    public function getTwilightZone(): int
    {
        if ($this->twilightZone === null) {
            $width = $this->getSurfaceWidth();

            $modulo = ((int)(time() * $this->getRotationFactor() / 100)) % TimeConstants::ONE_DAY_IN_SECONDS;
            $scaled = (int)ceil($modulo / (TimeConstants::ONE_DAY_IN_SECONDS / ($width * 2)));
            if ($scaled > $width) {
                $this->twilightZone = - ((2 * $width) - $scaled);
            } else {
                $this->twilightZone = $width - $scaled;
            }
        }

        return $this->twilightZone;
    }

    public function hasShields(): bool
    {
        return $this->hasBuildingWithFunction(BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR, [0, 1]);
    }

    public function getShieldState(): bool
    {
        return $this->hasActiveBuildingWithFunction(BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR)
            && $this->getShields() > 0;
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

    public function getRotationFactor(): ?int
    {
        return $this->rotation_factor;
    }

    public function setRotationFactor(?int $rotationFactor): ColonyInterface
    {
        $this->rotation_factor = $rotationFactor;
        return $this;
    }

    public function getRotationTime(): ?int
    {
        return 86400 * $this->getRotationFactor;
    }

    public function getSurfaceWidth(): ?int
    {
        return $this->surface_width;
    }

    public function setSurfaceWidth(int $surfaceWidth): ColonyInterface
    {
        $this->surface_width = $surfaceWidth;
        return $this;
    }

    public function getPlanetType(): PlanetTypeInterface
    {
        return $this->planetType;
    }

    public function setPlanetType(PlanetTypeInterface $planetType): ColonyInterface
    {
        $this->planetType = $planetType;
        return $this;
    }

    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage()->getValues(),
            function (int $sum, ColonyStorageInterface $storage): int {
                return $sum + $storage->getAmount();
            },
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

    public function getSystem(): StarSystemInterface
    {
        return $this->getStarsystemMap()->getSystem();
    }

    public function getEpsProduction(): int
    {
        // @todo refactor
        global $container;

        return $container->get(PlanetFieldRepositoryInterface::class)->getEnergyProductionByColony($this->getId());
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
     * @return ColonyStorageInterface[]
     */
    public function getBeamableStorage(): array
    {
        $filteredArray = array_filter(
            $this->getStorage()->getValues(),
            function (ColonyStorageInterface $storage): bool {
                return $storage->getCommodity()->isBeamable() === true;
            }
        );

        return $filteredArray;
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

    /**
     * @return ColonyProduction[]
     */
    public function getProductionRaw(): array
    {
        if ($this->productionRaw === null) {
            // @todo refactor
            global $container;
            $result = $container->get(BuildingGoodRepositoryInterface::class)->getProductionByColony(
                $this->getId(),
                $this->getPlanetType()->getId()
            );

            $this->productionRaw = [];
            foreach ($result as $data) {
                if (($data['gc'] + $data['pc']) != 0) {
                    $this->productionRaw[$data['goods_id']] = new ColonyProduction($data);
                }
            }
        }
        return $this->productionRaw;
    }

    public function setProductionRaw(array $array): void
    {
        $this->productionRaw = $array;
    }

    public function getProduction(): array
    {
        if ($this->production === null) {
            $this->production = $this->getProductionRaw();
            if (array_key_exists(CommodityTypeEnum::GOOD_FOOD, $this->production)) {
                if ($this->production[CommodityTypeEnum::GOOD_FOOD]->getProduction() - $this->getBevFood() == 0) {
                    unset($this->production[CommodityTypeEnum::GOOD_FOOD]);
                } else {
                    $this->production[CommodityTypeEnum::GOOD_FOOD]->lowerProduction($this->getBevFood());
                }
            } else {
                $obj = new ColonyProduction;
                $obj->setProduction(-$this->getBevFood());
                $obj->setGoodId(CommodityTypeEnum::GOOD_FOOD);
                $this->production[CommodityTypeEnum::GOOD_FOOD] = $obj;
            }
        }
        return $this->production;
    }

    public function getProductionSum(): int
    {
        if ($this->productionsum === null) {
            $sum = 0;
            foreach ($this->getProduction() as $key => $value) {
                if ($value->getGood()->getType() == CommodityTypeEnum::GOOD_TYPE_EFFECT) {
                    continue;
                }
                $sum += $value->getProduction();
            }
            $this->productionsum = $sum;
        }
        return $this->productionsum;
    }

    public function getOrbitShipList(int $userId): array
    {
        if ($this->shiplist === null) {
            $this->shiplist = [];

            // @todo refactor
            global $container;

            $shiplist = $container->get(ShipRepositoryInterface::class)->getByInnerSystemLocation(
                $this->getSystemsId(),
                $this->getSx(),
                $this->getSy()
            );

            foreach ($shiplist as $obj) {
                $this->shiplist[$obj->getFleetId()]['ships'][$obj->getId()] = $obj;
                if (!array_key_exists('name', $this->shiplist[$obj->getFleetId()])) {
                    if ($obj->getFleetId() == 0) {
                        $this->shiplist[$obj->getFleetId()]['name'] = _('Einzelschiffe');
                    } else {
                        $this->shiplist[$obj->getFleetId()]['name'] = $obj->getFleet()->getName();
                    }
                }
            }
        }
        return $this->shiplist;
    }

    public function isFree(): bool
    {
        return $this->getUserId() === GameEnum::USER_NOONE;
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

    public function getBevFood(): int
    {
        return (int) ceil(($this->getWorkers() + $this->getWorkless()) / ColonyTick::PEOPLE_FOOD);
    }

    public function getPopulation(): int
    {
        return $this->getWorkers() + $this->getWorkless();
    }

    public function getFreeHousing(): int
    {
        return $this->getMaxBev() - $this->getPopulation();
    }

    public function getImmigration(): int
    {
        if ($this->getImmigrationState() === false) {
            return 0;
        }
        // TBD: depends on social things. return dummy for now
        $im = ceil(($this->getMaxBev() - $this->getPopulation()) / 4);
        if ($this->getPopulation() + $im > $this->getMaxBev()) {
            $im = $this->getMaxBev() - $this->getPopulation();
        }
        if ($this->getPopulationLimit() > 0 && $this->getPopulation() + $im > $this->getPopulationLimit()) {
            $im = $this->getPopulationLimit() - $this->getPopulation();
        }
        if ($im < 0) {
            return 0;
        }
        return (int) round($im / 100 * $this->getPlanetType()->getBevGrowthRate());
    }

    public function getNegativeEffect(): int
    {
        return (int) ceil($this->getPopulation() / 70);
    }

    public function getPositiveEffectPrimary(): int
    {
        if ($this->positive_effect_primary === null) {
            $production = $this->getProduction();
            // TODO we should use a faction-factory...
            switch ($this->getUser()->getFactionId()) {
                case FactionEnum::FACTION_FEDERATION:
                    $key = ColonyEnum::GOOD_SATISFACTION_FED_PRIMARY;
                    break;
                case FactionEnum::FACTION_ROMULAN:
                    $key = ColonyEnum::GOOD_SATISFACTION_ROMULAN_PRIMARY;
                    break;
                case FactionEnum::FACTION_KLINGON:
                    $key = ColonyEnum::GOOD_SATISFACTION_KLINGON_PRIMARY;
                    break;
                case FactionEnum::FACTION_CARDASSIAN:
                    $key = ColonyEnum::GOOD_SATISFACTION_CARDASSIAN_PRIMARY;
                    break;
                case FactionEnum::FACTION_FERENGI:
                    $key = ColonyEnum::GOOD_SATISFACTION_FERENGI_PRIMARY;
                    break;
            }
            $this->positive_effect_primary = 0;
            if (!isset($production[$key])) {
                return 0;
            }
            $this->positive_effect_primary += $production[$key]->getProduction();
        }
        return $this->positive_effect_primary;
    }

    public function getPositiveEffectSecondary(): int
    {
        if ($this->positive_effect_secondary === null) {
            $production = $this->getProduction();
            $this->positive_effect_secondary = 0;
            // XXX we should use a faction-factory...
            switch ($this->getUser()->getFactionId()) {
                case FactionEnum::FACTION_FEDERATION:
                    $key = ColonyEnum::GOOD_SATISFACTION_FED_SECONDARY;
                    break;
                case FactionEnum::FACTION_ROMULAN:
                    $key = ColonyEnum::GOOD_SATISFACTION_ROMULAN_SECONDARY;
                    break;
                case FactionEnum::FACTION_KLINGON:
                    $key = ColonyEnum::GOOD_SATISFACTION_KLINGON_SECONDARY;
                    break;
                case FactionEnum::FACTION_CARDASSIAN:
                    $key = ColonyEnum::GOOD_SATISFACTION_CARDASSIAN_SECONDARY;
                    break;
                case FactionEnum::FACTION_FERENGI:
                    $key = ColonyEnum::GOOD_SATISFACTION_FERENGI_SECONDARY;
                    break;
            }
            if (!isset($production[$key])) {
                return 0;
            }
            $this->positive_effect_secondary += $production[$key]->getProduction();
        }
        return $this->positive_effect_secondary;
    }

    public function getCrewLimit(): int
    {
        return (int) floor(
            min(
                10 + max(
                    ($this->getPositiveEffectPrimary() - (4 * max(
                        0,
                        $this->getNegativeEffect() - $this->getPositiveEffectSecondary()
                    ))),
                    0
                ),
                $this->getWorkers()
            ) / 5
        );
    }

    public function clearCache(): void
    {
        $this->productionRaw = null;
        $this->production = null;
    }

    public function hasActiveBuildingWithFunction(int $function_id): bool
    {
        if (!isset($this->has_active_building_by_function[$function_id])) {
            $this->has_active_building_by_function[$function_id] = $this->hasBuildingWithFunction($function_id);
        }
        return $this->has_active_building_by_function[$function_id];
    }

    private function hasBuildingWithFunction(int $function_id, array $states = [1]): bool
    {
        return $this->getBuildingWithFunctionCount($function_id, $states) > 0;
    }

    public function getBuildingWithFunctionCount(int $function_id, array $states = [1]): int
    {
        // @todo refactor
        global $container;

        return $container
            ->get(PlanetFieldRepositoryInterface::class)
            ->getCountByColonyAndBuildingFunctionAndState(
                $this->getId(),
                [$function_id],
                $states
            );
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
}