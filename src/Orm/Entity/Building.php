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
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Building\BuildingEnum;
use Doctrine\Common\Collections\Criteria;


/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingRepository")
 * @Table(
 *     name="stu_buildings",
 *     indexes={
 *         @Index(name="eps_production_idx", columns={"eps_proc"}),
 *         @Index(name="buildmenu_column_idx", columns={"bm_col"}),
 *         @Index(name="building_research_idx", columns={"research_id"})
 *     }
 * )
 **/
class Building implements BuildingInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="string")
     *
     */
    private string $name = '';

    /**
     * @Column(type="smallint")
     *
     */
    private int $lager = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $eps = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $eps_cost = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $eps_proc = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $bev_pro = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $bev_use = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $integrity = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $research_id = 0;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $view = false;

    /**
     * @Column(type="integer")
     *
     */
    private int $buildtime = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $blimit = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $bclimit = 0;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $is_activateable = false;

    /**
     * @Column(type="smallint")
     *
     */
    private int $bm_col = 0;

    /**
     * @var ArrayCollection<int, BuildingCostInterface>
     *
     * @OneToMany(targetEntity="BuildingCost", mappedBy="building")
     */
    private Collection $costs;

    /**
     * @var ArrayCollection<int, BuildingFunctionInterface>
     *
     * @OneToMany(targetEntity="BuildingFunction", mappedBy="building", indexBy="function")
     */
    private Collection $functions;

    /**
     * @var ArrayCollection<int, BuildingCommodityInterface>
     *
     * @OneToMany(targetEntity="BuildingCommodity", mappedBy="building")
     */
    private Collection $commodities;

    /**
     * @var ArrayCollection<int, PlanetFieldTypeBuildingInterface>
     *
     * @OneToMany(targetEntity="PlanetFieldTypeBuilding", mappedBy="building", indexBy="type")
     */
    private Collection $possibleFieldTypes;

    public function __construct()
    {
        $this->costs = new ArrayCollection();
        $this->functions = new ArrayCollection();
        $this->commodities = new ArrayCollection();
        $this->possibleFieldTypes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): BuildingInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getStorage(): int
    {
        return $this->lager;
    }

    public function setStorage(int $storage): BuildingInterface
    {
        $this->lager = $storage;
        return $this;
    }

    public function getEpsStorage(): int
    {
        return $this->eps;
    }

    public function setEpsStorage(int $epsStorage): BuildingInterface
    {
        $this->eps = $epsStorage;
        return $this;
    }

    public function getEpsCost(): int
    {
        return $this->eps_cost;
    }

    public function setEpsCost(int $epsCost): BuildingInterface
    {
        $this->eps_cost = $epsCost;
        return $this;
    }

    public function getEpsProduction(): int
    {
        return $this->eps_proc;
    }

    public function setEpsProduction(int $epsProduction): BuildingInterface
    {
        $this->eps_proc = $epsProduction;
        return $this;
    }

    public function getHousing(): int
    {
        return $this->bev_pro;
    }

    public function setHousing(int $housing): BuildingInterface
    {
        $this->bev_pro = $housing;
        return $this;
    }

    public function getWorkers(): int
    {
        return $this->bev_use;
    }

    public function setWorkers(int $workers): BuildingInterface
    {
        $this->bev_use = $workers;
        return $this;
    }

    public function getIntegrity(): int
    {
        return $this->integrity;
    }

    public function setIntegrity(int $integrity): BuildingInterface
    {
        $this->integrity = $integrity;
        return $this;
    }

    public function getResearchId(): int
    {
        return $this->research_id;
    }

    public function setResearchId(int $researchId): BuildingInterface
    {
        $this->research_id = $researchId;
        return $this;
    }

    public function getView(): bool
    {
        return $this->view;
    }

    public function setView(bool $view): BuildingInterface
    {
        $this->view = $view;
        return $this;
    }

    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    public function setBuildtime(int $buildtime): BuildingInterface
    {
        $this->buildtime = $buildtime;
        return $this;
    }

    public function getLimit(): int
    {
        return $this->blimit;
    }

    public function setLimit(int $limit): BuildingInterface
    {
        $this->blimit = $limit;
        return $this;
    }

    public function getLimitColony(): int
    {
        return $this->bclimit;
    }

    public function setLimitColony(int $limitColony): BuildingInterface
    {
        $this->bclimit = $limitColony;
        return $this;
    }

    public function getIsActivateable(): bool
    {
        return $this->is_activateable;
    }

    public function setIsActivateable(bool $isActivateable): BuildingInterface
    {
        $this->is_activateable = $isActivateable;
        return $this;
    }

    public function getBmCol(): int
    {
        return $this->bm_col;
    }

    public function setBmCol(int $buildmenuColumn): BuildingInterface
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

    public function getBuildableFields(): Collection
    {
        return $this->possibleFieldTypes;
    }

    public function getCosts(): Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['commodity_id' => Criteria::ASC]);

        return $this->costs->matching($criteria);
    }

    public function getCommodities(): Collection
    {
        return $this->commodities;
    }

    /**
     * @return Collection<int, BuildingFunctionInterface>
     */
    public function getFunctions(): Collection
    {
        return $this->functions;
    }

    public function isRemovable(): bool
    {
        return $this->getFunctions()->containsKey(BuildingEnum::BUILDING_FUNCTION_CENTRAL) === false;
    }

    public function getShieldCapacity(): ?int
    {
        if ($this->getFunctions()->containsKey(BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR) === true) {
            return BuildingEnum::SHIELD_GENERATOR_CAPACITY;
        }

        if ($this->getFunctions()->containsKey(BuildingEnum::BUILDING_FUNCTION_SHIELD_BATTERY) === true) {
            return BuildingEnum::SHIELD_BATTERY_CAPACITY;
        }
        return null;
    }
}
