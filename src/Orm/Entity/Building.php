<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use ColonyData;
use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Repository\BuildingCostRepositoryInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\BuildingGoodRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldTypeBuildingRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingRepository")
 * @Table(
 *     name="stu_buildings",
 *     indexes={
 *         @Index(name="eps_production_idx", columns={"eps_proc"}),
 *         @Index(name="buildmenu_column_idx", columns={"bm_col"}),
 *         @Index(name="research_idx", columns={"research_id"})
 *     }
 * )
 **/
class Building implements BuildingInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="smallint") * */
    private $lager = 0;

    /** @Column(type="smallint") * */
    private $eps = 0;

    /** @Column(type="smallint") * */
    private $eps_cost = 0;

    /** @Column(type="smallint") * */
    private $eps_proc = 0;

    /** @Column(type="smallint") * */
    private $bev_pro = 0;

    /** @Column(type="smallint") * */
    private $bev_use = 0;

    /** @Column(type="smallint") * */
    private $integrity = 0;

    /** @Column(type="integer") * */
    private $research_id = 0;

    /** @Column(type="boolean") * */
    private $view = false;

    /** @Column(type="integer") * */
    private $buildtime = 0;

    /** @Column(type="smallint") * */
    private $blimit = 0;

    /** @Column(type="smallint") * */
    private $bclimit = 0;

    /** @Column(type="boolean") * */
    private $is_activateable = false;

    /** @Column(type="smallint") * */
    private $bm_col = 0;

    /** @Column(type="smallint") * */
    private $is_base = 0;

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

    public function getIsBase(): int
    {
        return $this->is_base;
    }

    public function setIsBase($isBase): BuildingInterface
    {
        $this->is_base = $isBase;
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

    public function getEpsProductionDisplay(): string
    {
        if ($this->getEpsProduction() < 0) {
            return (string)$this->getEpsProduction();
        }
        return '+' . $this->getEpsProduction();
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

    private $buildfields = null;

    public function getBuildableFields(): array
    {
        if ($this->buildfields === null) {
            $this->buildfields = [];
            // @todo refactor
            global $container;

            $this->buildfields = array_map(
                function (PlanetFieldTypeBuildingInterface $fieldTypeBuilding): int {
                    return $fieldTypeBuilding->getFieldTypeId();
                },
                $container->get(PlanetFieldTypeBuildingRepositoryInterface::class)->getByBuilding((int)$this->getId())
            );
        }
        return $this->buildfields;
    }

    private $costs = null;

    /**
     * @return BuildingCostInterface[]
     */
    public function getCosts(): array
    {
        if ($this->costs === null) {
            // @todo refactor
            global $container;

            $this->costs = $container->get(BuildingCostRepositoryInterface::class)->getByBuilding((int)$this->getId());
        }
        return $this->costs;
    }

    private $goods = null;

    /**
     * @return BuildingGoodInterface[]
     */
    public function getGoods(): array
    {
        if ($this->goods === null) {
            // @todo refactor
            global $container;

            $this->goods = $container->get(BuildingGoodRepositoryInterface::class)->getByBuilding((int)$this->getId());
        }
        return $this->goods;
    }

    private $functions;

    /**
     * @return BuildingFunctionInterface[]
     */
    public function getFunctions(): array
    {
        if ($this->functions === null) {
            $this->functions = [];

            // @todo refactor
            global $container;

            $result = $container->get(BuildingFunctionRepositoryInterface::class)->getByBuilding((int)$this->getId());
            foreach ($result as $function) {
                $this->functions[$function->getFunction()] = $function;
            }
        }
        return $this->functions;
    }

    public function postDeactivation(ColonyData $colony): void
    {
        // @todo refactor
        global $container;

        $buildingFunctionActionMapper = $container->get(BuildingFunctionActionMapperInterface::class);

        foreach ($this->getFunctions() as $function) {
            $buildingFunctionId = $function->getFunction();

            $handler = $buildingFunctionActionMapper->map($buildingFunctionId);
            if ($handler !== null) {
                $handler->deactivate((int)$colony->getId(), $buildingFunctionId);
            }
        }
    }

    public function postActivation(ColonyData $colony): void
    {
        // @todo refactor
        global $container;

        $buildingFunctionActionMapper = $container->get(BuildingFunctionActionMapperInterface::class);

        foreach ($this->getFunctions() as $function) {
            $buildingFunctionId = $function->getFunction();

            $handler = $buildingFunctionActionMapper->map($buildingFunctionId);
            if ($handler !== null) {
                $handler->activate((int)$colony->getId(), $buildingFunctionId);
            }
        }
    }

    public function isRemoveAble(): bool
    {
        return !array_key_exists(BUILDING_FUNCTION_CENTRAL, $this->getFunctions());
    }
}
