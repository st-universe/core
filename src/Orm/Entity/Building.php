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
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\Shields\ColonyShieldingManager;
use Stu\Orm\Repository\BuildingRepository;

#[Table(name: 'stu_buildings')]
#[Index(name: 'eps_production_idx', columns: ['eps_proc'])]
#[Index(name: 'buildmenu_column_idx', columns: ['bm_col'])]
#[Index(name: 'building_research_idx', columns: ['research_id'])]
#[Entity(repositoryClass: BuildingRepository::class)]
class Building
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'smallint')]
    private int $lager = 0;

    #[Column(type: 'smallint')]
    private int $eps = 0;

    #[Column(type: 'smallint')]
    private int $eps_cost = 0;

    #[Column(type: 'smallint')]
    private int $eps_proc = 0;

    #[Column(type: 'smallint')]
    private int $bev_pro = 0;

    #[Column(type: 'smallint')]
    private int $bev_use = 0;

    #[Column(type: 'smallint')]
    private int $integrity = 0;

    #[Column(type: 'integer')]
    private int $research_id = 0;

    #[Column(type: 'boolean')]
    private bool $view = false;

    #[Column(type: 'integer')]
    private int $buildtime = 0;

    #[Column(type: 'smallint')]
    private int $blimit = 0;

    #[Column(type: 'smallint')]
    private int $bclimit = 0;

    #[Column(type: 'boolean')]
    private bool $is_activateable = false;

    #[Column(type: 'smallint')]
    private int $bm_col = 0;

    /**
     * @var ArrayCollection<int, BuildingCost>
     */
    #[OneToMany(targetEntity: BuildingCost::class, mappedBy: 'building')]
    #[OrderBy(['commodity_id' => 'ASC'])]
    private Collection $costs;

    /**
     * @var ArrayCollection<int, BuildingFunction>
     */
    #[OneToMany(targetEntity: BuildingFunction::class, mappedBy: 'building', indexBy: 'function')]
    private Collection $functions;

    /**
     * @var ArrayCollection<int, BuildingCommodity>
     */
    #[OneToMany(targetEntity: BuildingCommodity::class, mappedBy: 'building', indexBy: 'commodity_id')]
    #[OrderBy(['commodity_id' => 'ASC'])]
    private Collection $commodities;

    /**
     * @var ArrayCollection<int, PlanetFieldTypeBuilding>
     */
    #[OneToMany(targetEntity: PlanetFieldTypeBuilding::class, mappedBy: 'building', indexBy: 'type')]
    private Collection $possibleFieldTypes;

    /**
     * @var ArrayCollection<int, ColonyClassRestriction>
     */
    #[OneToMany(mappedBy: 'building', targetEntity: ColonyClassRestriction::class)]
    private Collection $restrictions;


    public function __construct()
    {
        $this->costs = new ArrayCollection();
        $this->functions = new ArrayCollection();
        $this->commodities = new ArrayCollection();
        $this->possibleFieldTypes = new ArrayCollection();
        $this->restrictions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Building
    {
        $this->name = $name;
        return $this;
    }

    public function getStorage(): int
    {
        return $this->lager;
    }

    public function setStorage(int $storage): Building
    {
        $this->lager = $storage;
        return $this;
    }

    public function getEpsStorage(): int
    {
        return $this->eps;
    }

    public function setEpsStorage(int $epsStorage): Building
    {
        $this->eps = $epsStorage;
        return $this;
    }

    public function getEpsCost(): int
    {
        return $this->eps_cost;
    }

    public function setEpsCost(int $epsCost): Building
    {
        $this->eps_cost = $epsCost;
        return $this;
    }

    public function getEpsProduction(): int
    {
        return $this->eps_proc;
    }

    public function setEpsProduction(int $epsProduction): Building
    {
        $this->eps_proc = $epsProduction;
        return $this;
    }

    public function getHousing(): int
    {
        return $this->bev_pro;
    }

    public function setHousing(int $housing): Building
    {
        $this->bev_pro = $housing;
        return $this;
    }

    public function getWorkers(): int
    {
        return $this->bev_use;
    }

    public function setWorkers(int $workers): Building
    {
        $this->bev_use = $workers;
        return $this;
    }

    public function getIntegrity(): int
    {
        return $this->integrity;
    }

    public function setIntegrity(int $integrity): Building
    {
        $this->integrity = $integrity;
        return $this;
    }

    public function getResearchId(): int
    {
        return $this->research_id;
    }

    public function setResearchId(int $researchId): Building
    {
        $this->research_id = $researchId;
        return $this;
    }

    public function getView(): bool
    {
        return $this->view;
    }

    public function setView(bool $view): Building
    {
        $this->view = $view;
        return $this;
    }

    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    public function setBuildtime(int $buildtime): Building
    {
        $this->buildtime = $buildtime;
        return $this;
    }

    public function getLimit(): int
    {
        return $this->blimit;
    }

    public function setLimit(int $limit): Building
    {
        $this->blimit = $limit;
        return $this;
    }

    public function getLimitColony(): int
    {
        return $this->bclimit;
    }

    public function setLimitColony(int $limitColony): Building
    {
        $this->bclimit = $limitColony;
        return $this;
    }

    public function getIsActivateable(): bool
    {
        return $this->is_activateable;
    }

    public function setIsActivateable(bool $isActivateable): Building
    {
        $this->is_activateable = $isActivateable;
        return $this;
    }

    public function getBmCol(): int
    {
        return $this->bm_col;
    }

    public function setBmCol(int $buildmenuColumn): Building
    {
        $this->bm_col = $buildmenuColumn;
        return $this;
    }

    public function isActivateable(): bool
    {
        return $this->getIsActivateable();
    }

    public function isViewable(): bool
    {
        return $this->getView();
    }

    public function getBuildingType(): int
    {
        // return 0 for now
        return 0;
    }

    public function getEpsProductionCss(): string
    {
        if ($this->getEpsProduction() < 0) {
            return 'negative';
        }
        if ($this->getEpsProduction() > 0) {
            return 'positive';
        }
        return '';
    }

    public function hasLimit(): bool
    {
        return $this->getLimit() > 0;
    }

    public function hasLimitColony(): bool
    {
        return $this->getLimitColony() > 0;
    }

    /**
     * @return Collection<int, PlanetFieldTypeBuilding>
     */
    public function getBuildableFields(): Collection
    {
        return $this->possibleFieldTypes;
    }

    /**
     * @return Collection<int, BuildingCost>
     */
    public function getCosts(): Collection
    {
        return $this->costs;
    }

    /**
     * @return Collection<int, BuildingCommodity>
     */
    public function getCommodities(): Collection
    {
        return $this->commodities;
    }

    /**
     * @return Collection<int, BuildingFunction>
     */
    public function getFunctions(): Collection
    {
        return $this->functions;
    }

    public function isRemovable(): bool
    {
        return !$this->getFunctions()->containsKey(BuildingFunctionEnum::COLONY_CENTRAL->value)
            && !$this->getFunctions()->containsKey(BuildingFunctionEnum::BASE_CAMP->value);
    }

    public function getShieldCapacity(): ?int
    {
        if ($this->getFunctions()->containsKey(BuildingFunctionEnum::SHIELD_GENERATOR->value) === true) {
            return ColonyShieldingManager::SHIELD_GENERATOR_CAPACITY;
        }

        if ($this->getFunctions()->containsKey(BuildingFunctionEnum::SHIELD_BATTERY->value) === true) {
            return ColonyShieldingManager::SHIELD_BATTERY_CAPACITY;
        }
        return null;
    }

    /**
     * @return Collection<int, ColonyClassRestriction>
     */
    public function getRestrictions(): Collection
    {
        return $this->restrictions;
    }
}
