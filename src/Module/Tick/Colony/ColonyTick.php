<?php

namespace Stu\Module\Tick\Colony;

use ColfieldData;
use Colfields;
use ColonyData;
use Stu\Lib\ColonyProduction\ColonyProduction;
use PM;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Research\ResearchState;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ColonyTick implements ColonyTickInterface
{
    private $commodityRepository;

    private $researchedRepository;

    private $shipRumpUserRepository;

    private $moduleQueueRepository;

    private $msg = [];

    public function __construct(
        CommodityRepositoryInterface $commodityRepository,
        ResearchedRepositoryInterface $researchedRepository,
        ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        ModuleQueueRepositoryInterface $moduleQueueRepository
    ) {
        $this->commodityRepository = $commodityRepository;
        $this->researchedRepository = $researchedRepository;
        $this->shipRumpUserRepository = $shipRumpUserRepository;
        $this->moduleQueueRepository = $moduleQueueRepository;
    }

    public function work(ColonyData $colony): void
    {
        $this->mainLoop($colony);
        $this->proceedStorage($colony);

        $colony->save();

        $this->proceedModules($colony);
        $this->sendMessages($colony);
    }

    private function mainLoop(ColonyData $colony)
    {
        $i = 1;
        $storage = $colony->getStorage();
        $production = $colony->getProductionRaw();

        while (true) {
            $rewind = 0;
            foreach ($production as $commodityId => $pro) {
                if ($pro->getProduction() >= 0) {
                    continue;
                }
                $storageItem = $storage[$pro->getGoodId()] ?? null;
                if ($storageItem !== null && $storageItem->getAmount() + $pro->getProduction() >= 0) {
                    continue;
                }

                $field = $this->getBuildingToDeactivateByGood($colony, $commodityId);
                //echo $i." hit by good ".$field->getFieldId()." - produce ".$pro->getProduction()." MT ".microtime()."\n";
                $this->deactivateBuilding($colony, $field, $commodityId);
                $rewind = 1;
            }
            if ($rewind == 0 && $colony->getEpsProduction() < 0 && $colony->getEps() + $colony->getEpsProduction() < 0) {
                $field = $this->getBuildingToDeactivateByEpsUsage($colony,);
                //echo $i." hit by eps ".$field->getFieldId()." - complete usage ".$colony->getEpsProduction()." - usage ".$field->getBuilding()->getEpsProduction()." MT ".microtime()."\n";
                $this->deactivateBuilding($colony, $field, 0);
                $rewind = 1;
            }
            if ($rewind == 1) {
                reset($production);
                $i++;
                if ($i == 100) {
                    // SECURITY
                    //echo "HIT SECURITY BREAK\n";
                    break;
                }
                continue;
            }
            break;
        }
        $colony->setEps($colony->getEps() + $colony->getEpsProduction());
    }

    private function deactivateBuilding(ColonyData $colony, ColfieldData $field, int $commodityId): void
    {
        if ($commodityId === 0) {
            $ext = "Energie";
        } else {
            $ext = $this->commodityRepository->find($commodityId)->getName();
        }

        $this->msg[] = $field->getBuilding()->getName() . " auf Feld " . $field->getFieldId() . " deaktiviert (Mangel an " . $ext . ")";

        $colony->upperWorkless($field->getBuilding()->getWorkers());
        $colony->lowerWorkers($field->getBuilding()->getWorkers());
        $colony->lowerMaxBev($field->getBuilding()->getHousing());
        $colony->setEpsProduction($colony->getEpsProduction() - $field->getBuilding()->getEpsProduction());
        $this->mergeProduction($colony, $field->getBuilding()->getGoods());
        $field->getBuilding()->postDeactivation($colony);

        $field->setActive(0);
        $field->save();
    }

    private function getBuildingToDeactivateByGood(ColonyData $colony, int $commodityId): ColfieldData
    {
        return Colfields::getBy("colonies_id=" . $colony->getId() . " AND aktiv=1 AND buildings_id IN (SELECT buildings_id FROM stu_buildings_goods WHERE goods_id=" . $commodityId . " AND count<0)");
    }

    private function getBuildingToDeactivateByEpsUsage(ColonyData $colony): ColfieldData
    {
        return Colfields::getBy("colonies_id=" . $colony->getId() . " AND aktiv=1 AND buildings_id IN (SELECT id FROM stu_buildings WHERE eps_proc<0)");
    }

    private function proceedStorage(ColonyData $colony): void
    {
        $emigrated = 0;
        $production = $this->proceedFood($colony);
        $sum = $colony->getStorageSum();
        $storage = $colony->getStorage();

        foreach ($production as $commodityId => $obj) {
            if ($obj->getProduction() >= 0) {
                continue;
            }
            if ($commodityId == CommodityTypeEnum::GOOD_FOOD) {
                $storageItem = $storage[CommodityTypeEnum::GOOD_FOOD] ?? null;
                if ($storageItem === null && $obj->getProduction() < 1) {
                    $this->proceedEmigration($colony, true);
                    $emigrated = 1;
                } elseif ($storageItem->getAmount() + $obj->getProduction() < 0) {
                    $this->proceedEmigration($colony, true, abs($storageItem->getAmount() + $obj->getProduction()));
                    $emigrated = 1;
                }
            }
            $colony->lowerStorage($commodityId, abs($obj->getProduction()));
            $sum -= abs($obj->getProduction());
        }
        foreach ($production as $commodityId => $obj) {
            if ($obj->getProduction() <= 0 || !$obj->getGood()->isSaveable()) {
                continue;
            }
            if ($sum >= $colony->getMaxStorage()) {
                break;
            }
            if ($sum + $obj->getProduction() > $colony->getMaxStorage()) {
                $colony->upperStorage($commodityId, $colony->getMaxStorage() - $sum);
                break;
            }
            $colony->upperStorage($commodityId, $obj->getProduction());
            $sum += $obj->getProduction();
        }

        $current_research = $this->researchedRepository->getCurrentResearch($colony->getUserId());

        if ($current_research && $current_research->getActive()) {
            if (isset($production[$current_research->getResearch()->getGoodId()])) {
                (new ResearchState($this->researchedRepository, $this->shipRumpUserRepository)
                )->advance(
                    $current_research,
                    $production[$current_research->getResearch()->getGoodId()]->getProduction()
                );
            }
        }
        if ($colony->hasOverpopulation()) {
            $this->proceedEmigration($colony);
            return;
        }
        if ($colony->getPopulationLimit() > 0 && $colony->getPopulation() > $colony->getPopulationLimit() && $colony->getWorkless()) {
            if (($free = ($colony->getPopulationLimit() - $colony->getWorkers())) > 0) {
                $this->msg[] = sprintf(
                    _('Es sind %d Arbeitslose ausgewandert'),
                    ($colony->getWorkless() - $free)
                );
                $colony->setWorkless($free);
            } else {
                $this->msg[] = _('Es sind alle Arbeitslosen ausgewandert');
                $colony->setWorkless(0);
            }
        }
        if ($emigrated == 0) {
            $this->proceedImmigration($colony);
        }
    }

    private function proceedModules(ColonyData $colony): void
    {
        foreach ($this->moduleQueueRepository->getByColony((int) $colony->getId()) as $queue) {
            if ($colony->hasActiveBuildingWithFunction($queue->getBuildingFunction())) {
                $colony->upperStorage($queue->getModule()->getGoodId(), $queue->getAmount());
                $this->msg[] = sprintf(
                    _('Es wurden %d %s hergestellt'),
                    $queue->getAmount(),
                    $queue->getModule()->getName()
                );
                $this->moduleQueueRepository->delete($queue);
            }
        }
    }

    /**
     * @return ColonyProduction[]
     */
    private function proceedFood(ColonyData $colony): array
    {
        $foodvalue = $colony->getBevFood();
        $prod = &$colony->getProductionRaw();
        if (!array_key_exists(CommodityTypeEnum::GOOD_FOOD, $prod)) {
            $obj = new ColonyProduction();
            $obj->setGoodId(CommodityTypeEnum::GOOD_FOOD);
            $obj->lowerProduction($foodvalue);
            $prod[CommodityTypeEnum::GOOD_FOOD] = $obj;
        } else {
            $prod[CommodityTypeEnum::GOOD_FOOD]->lowerProduction($foodvalue);
        }
        return $prod;
    }

    private function proceedImmigration(ColonyData $colony): void
    {
        // @todo
        $im = $colony->getImmigration();
        $colony->upperWorkless($im);
    }

    private function proceedEmigration(ColonyData $colony, $foodrelated = false, $foodmissing = false)
    {
        if ($colony->getWorkless()) {
            if ($foodmissing > 0) {
                $bev = $foodmissing * ColonyData::PEOPLE_FOOD;
                if ($bev > $colony->getWorkless()) {
                    $bev = $colony->getWorkless();
                }
            } else {
                if ($foodrelated) {
                    $bev = $colony->getWorkless();
                } else {
                    $bev = rand(1, $colony->getWorkless());
                }
            }
            $colony->lowerWorkless($bev);
            if ($foodrelated) {
                $this->msg[] = $bev . " Einwohner sind aufgrund des Nahrungsmangels ausgewandert";
            } else {
                $this->msg[] = $bev . " Einwohner sind ausgewandert";
            }
        }
    }

    private function sendMessages(ColonyData $colony): void
    {
        if ($this->msg === []) {
            return;
        }
        $text = "Tickreport der Kolonie " . $colony->getNameWithoutMarkup() . "\n";
        foreach ($this->msg as $key => $msg) {
            $text .= $msg . "\n";
        }
        PM::sendPM(USER_NOONE, $colony->getUserId(), $text, PM_SPECIAL_COLONY);

        $this->msg = [];
    }

    private function mergeProduction(ColonyData $colony, array $commodityProduction): void
    {
        $prod = $colony->getProductionRaw();
        foreach ($commodityProduction as $obj) {
            $commodityId = $obj->getGoodId();
            if (!array_key_exists($commodityId, $prod)) {
                $data = new ColonyProduction;
                $data->setGoodId($commodityId);
                $data->setProduction($obj->getAmount() * -1);
                $colony->setProductionRaw($colony->getProductionRaw() + array($commodityId => $data));
            } else {
                if ($obj->getAmount() < 0) {
                    $prod[$commodityId]->upperProduction(abs($obj->getAmount()));
                } else {
                    $prod[$commodityId]->lowerProduction($obj->getAmount());
                }
            }
        }
    }

}
