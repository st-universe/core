<?php

namespace Stu\Module\Tick\Colony;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Game\GameEnum;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Research\ResearchState;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ColonyTick implements ColonyTickInterface
{
    public const PEOPLE_FOOD = 7;

    private ResearchedRepositoryInterface $researchedRepository;

    private ShipRumpUserRepositoryInterface $shipRumpUserRepository;

    private ModuleQueueRepositoryInterface $moduleQueueRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private CreateDatabaseEntryInterface $createDatabaseEntry;

    private BuildingManagerInterface $buildingManager;

    private CrewCreatorInterface $crewCreator;

    private ShipCreatorInterface $shipCreator;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private LoggerUtilInterface $loggerUtil;

    private array $commodityArray;

    private array $msg = [];

    private EntityManagerInterface $entityManager;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository,
        ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        ModuleQueueRepositoryInterface $moduleQueueRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        CreateDatabaseEntryInterface $createDatabaseEntry,
        BuildingManagerInterface $buildingManager,
        CrewCreatorInterface $crewCreator,
        ShipCreatorInterface $shipCreator,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        LoggerUtilInterface $loggerUtil,
        EntityManagerInterface $entityManager
    ) {
        $this->researchedRepository = $researchedRepository;
        $this->shipRumpUserRepository = $shipRumpUserRepository;
        $this->moduleQueueRepository = $moduleQueueRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->createDatabaseEntry = $createDatabaseEntry;
        $this->buildingManager = $buildingManager;
        $this->crewCreator = $crewCreator;
        $this->shipCreator = $shipCreator;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->loggerUtil = $loggerUtil;
        $this->entityManager = $entityManager;
    }

    public function work(ColonyInterface $colony, array $commodityArray): void
    {
        $this->loggerUtil->init('tick');
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $this->commodityArray = $commodityArray;

        $this->mainLoop($colony);

        $this->proceedStorage($colony);

        $this->colonyRepository->save($colony, false);

        $this->proceedModules($colony);
        $this->sendMessages($colony);

        $endTime = microtime(true);

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("Colony-Id: %6d, seconds: %F", $colony->getId(), $endTime - $startTime));
        }
    }

    private function mainLoop(ColonyInterface $colony)
    {
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $i = 1;
        $storage = $colony->getStorage();

        while (true) {
            $rewind = 0;
            $production = $colony->getProductionRaw();
            foreach ($production as $commodityId => $pro) {
                if ($pro->getProduction() >= 0) {
                    continue;
                }
                $storageItem = $storage[$pro->getGoodId()] ?? null;
                if ($storageItem !== null && $storageItem->getAmount() + $pro->getProduction() >= 0) {
                    continue;
                }
                //echo "coloId:" . $colony->getId() . ", production:" . $pro->getProduction() . ", commodityId:" . $commodityId . ", commodity:" . $this->commodityArray[$commodityId]->getName() . "\n";
                $field = $this->getBuildingToDeactivateByGood($colony, $commodityId);
                // echo $i." hit by good ".$field->getFieldId()." - produce ".$pro->getProduction()." MT ".microtime()."\n";
                $this->deactivateBuilding($colony, $field, $this->commodityArray[$commodityId]);
                $rewind = 1;
            }
            if ($rewind == 0 && $colony->getEpsProduction() < 0 && $colony->getEps() + $colony->getEpsProduction() < 0) {
                $field = $this->getBuildingToDeactivateByEpsUsage($colony,);
                //echo $i . " hit by eps " . $field->getFieldId() . " - complete usage " . $colony->getEpsProduction() . " - usage " . $field->getBuilding()->getEpsProduction() . " MT " . microtime() . "\n";
                $this->deactivateBuilding($colony, $field);
                $rewind = 1;
            }
            if ($rewind == 1) {
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
        $colony->setEps(min($colony->getMaxEps(), $colony->getEps() + $colony->getEpsProduction()));

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tmainLoop, seconds: %F", $endTime - $startTime));
        }
    }

    private function deactivateBuilding(ColonyInterface $colony, PlanetFieldInterface $field, CommodityInterface $commodity = null): void
    {
        if ($commodity === null) {
            $ext = "Energie";
        } else {
            $ext = $commodity->getName();
        }
        $building = $field->getBuilding();

        $this->buildingManager->deactivate($field);
        $this->entityManager->flush();

        $this->mergeProduction($colony, $building->getGoods());

        $this->msg[] = $building->getName() . " auf Feld " . $field->getFieldId() . " deaktiviert (Mangel an " . $ext . ")";
    }

    private function getBuildingToDeactivateByGood(ColonyInterface $colony, int $commodityId): PlanetFieldInterface
    {
        $fields = $this->planetFieldRepository->getCommodityConsumingByColonyAndCommodity(
            $colony->getId(),
            $commodityId,
            [1]
        );

        return current($fields);
    }

    private function getBuildingToDeactivateByEpsUsage(ColonyInterface $colony): PlanetFieldInterface
    {
        $fields = $this->planetFieldRepository->getEnergyConsumingByColony($colony->getId(), [1], 1);

        return current($fields);
    }

    private function proceedStorage(ColonyInterface $colony): void
    {
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $emigrated = 0;
        $production = $this->proceedFood($colony);
        $sum = $colony->getStorageSum();
        $storage = $colony->getStorage();

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        foreach ($production as $commodityId => $obj) {
            $amount = $obj->getProduction();

            if ($amount >= 0) {
                continue;
            }

            $amount = abs($amount);

            if ($commodityId == CommodityTypeEnum::GOOD_FOOD) {
                $storageItem = $storage[CommodityTypeEnum::GOOD_FOOD] ?? null;
                if ($storageItem === null && $amount > 0) {
                    $this->proceedEmigration($colony, true);
                    $emigrated = 1;
                    $amount = 0;
                } elseif ($storageItem->getAmount() - $amount < 0) {
                    $this->proceedEmigration($colony, true, abs($storageItem->getAmount() - $amount));
                    $emigrated = 1;
                    $amount = $storageItem->getAmount();
                }
            }

            if ($amount > 0) {
                $this->colonyStorageManager->lowerStorage(
                    $colony,
                    $this->commodityArray[$commodityId],
                    $amount
                );
                $sum -= $amount;
            }
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tforeach1, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        foreach ($production as $commodityId => $obj) {
            if ($this->loggerUtil->doLog()) {
                $startTimeC = microtime(true);
            }

            $commodity = $this->commodityArray[$commodityId];
            if ($obj->getProduction() <= 0 || !$commodity->isSaveable()) {
                continue;
            }
            if ($sum >= $colony->getMaxStorage()) {
                if ($colony->getUser()->isStorageNotification()) {
                    //TODO send pm and or email
                }
                break;
            }
            if ($sum + $obj->getProduction() > $colony->getMaxStorage()) {
                $this->colonyStorageManager->upperStorage(
                    $colony,
                    $commodity,
                    $colony->getMaxStorage() - $sum
                );
                break;
            }
            if ($this->loggerUtil->doLog()) {
                $startTimeM = microtime(true);
            }
            $this->colonyStorageManager->upperStorage(
                $colony,
                $commodity,
                $obj->getProduction()
            );
            if ($this->loggerUtil->doLog()) {
                $endTimeM = microtime(true);
                $this->loggerUtil->log(sprintf("\t\t\tupper, seconds: %F", $endTimeM - $startTimeM));
            }
            $sum += $obj->getProduction();
            if ($this->loggerUtil->doLog()) {
                $endTimeC = microtime(true);
                $this->loggerUtil->log(sprintf("\t\tcommodity: %s, seconds: %F", $commodity->getName(), $endTimeC - $startTimeC));
            }
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tforeach2, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $current_research = $this->researchedRepository->getCurrentResearch($colony->getUserId());

        if ($current_research && $current_research->getActive()) {
            if (isset($production[$current_research->getResearch()->getGoodId()])) {
                (new ResearchState(
                    $this->researchedRepository,
                    $this->shipRumpUserRepository,
                    $this->privateMessageSender,
                    $this->createDatabaseEntry,
                    $this->crewCreator,
                    $this->shipCreator,
                    $this->colonyRepository,
                    $this->shipRepository,
                    $this->shipSystemManager,
                    $this->entityManager
                ))->advance(
                    $current_research,
                    $production[$current_research->getResearch()->getGoodId()]->getProduction()
                );
            }
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tresearch, seconds: %F", $endTime - $startTime));
        }

        if ($colony->getPopulation() > $colony->getMaxBev()) {
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

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tstorage, seconds: %F", $endTime - $startTime));
        }
    }

    private function proceedModules(ColonyInterface $colony): void
    {
        foreach ($this->moduleQueueRepository->getByColony((int) $colony->getId()) as $queue) {
            if ($colony->hasActiveBuildingWithFunction($queue->getBuildingFunction())) {
                $this->colonyStorageManager->upperStorage(
                    $colony,
                    $queue->getModule()->getCommodity(),
                    $queue->getAmount()
                );

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
    private function proceedFood(ColonyInterface $colony): array
    {
        $foodvalue = $colony->getBevFood();
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $prod = &$colony->getProductionRaw();
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tgetProd, seconds: %F", $endTime - $startTime));
        }
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

    private function proceedImmigration(ColonyInterface $colony): void
    {
        // @todo
        $im = $colony->getImmigration();
        $colony->setWorkless($colony->getWorkless() + $im);
    }

    private function proceedEmigration(ColonyInterface $colony, $foodrelated = false, $foodmissing = false)
    {
        if ($colony->getWorkless()) {
            if ($foodmissing > 0) {
                $bev = $foodmissing * self::PEOPLE_FOOD;
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
            $colony->setWorkless($colony->getWorkless() - $bev);
            if ($foodrelated) {
                $this->msg[] = $bev . " Einwohner sind aufgrund des Nahrungsmangels ausgewandert";
            } else {
                $this->msg[] = $bev . " Einwohner sind ausgewandert";
            }
        }
    }

    private function sendMessages(ColonyInterface $colony): void
    {
        if ($this->msg === []) {
            return;
        }
        $text = "Tickreport der Kolonie " . $colony->getName() . "\n";
        foreach ($this->msg as $msg) {
            $text .= $msg . "\n";
        }

        $href = sprintf(_('colony.php?SHOW_COLONY=1&id=%d'), $colony->getId());

        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            (int) $colony->getUserId(),
            $text,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
            $href
        );

        $this->msg = [];
    }

    private function mergeProduction(ColonyInterface $colony, Collection $commodityProduction): void
    {
        $prod = $colony->getProductionRaw();
        foreach ($commodityProduction as $obj) {
            $commodityId = $obj->getGoodId();
            if (!array_key_exists($commodityId, $prod)) {
                $data = new ColonyProduction;
                $data->setGoodId($commodityId);
                $data->setProduction($obj->getAmount() * -1);

                $prod[$commodityId] = $data;
                $colony->setProductionRaw($prod);
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
