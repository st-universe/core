<?php

namespace Stu\Module\Tick\Colony;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Research\ResearchState;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\BuildingCommodityInterface;
use Stu\Orm\Entity\ColonyDepositMiningInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyDepositMiningRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ColonyTick implements ColonyTickInterface
{
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

    private CreateUserAwardInterface $createUserAward;

    private ColonyDepositMiningRepositoryInterface $colonyDepositMiningRepository;

    private EntityManagerInterface $entityManager;

    private LoggerUtilInterface $loggerUtil;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    private array $commodityArray;

    private array $msg = [];

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
        CreateUserAwardInterface $createUserAward,
        ColonyDepositMiningRepositoryInterface $colonyDepositMiningRepository,
        EntityManagerInterface $entityManager,
        ColonyLibFactoryInterface $colonyLibFactory,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
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
        $this->createUserAward = $createUserAward;
        $this->colonyDepositMiningRepository = $colonyDepositMiningRepository;
        $this->entityManager = $entityManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->colonyLibFactory = $colonyLibFactory;
        $this->colonyFunctionManager = $colonyFunctionManager;
    }

    public function work(ColonyInterface $colony, array $commodityArray): void
    {
        $doLog = $this->loggerUtil->doLog();
        if ($doLog) {
            $startTime = microtime(true);
        }

        $this->commodityArray = $commodityArray;

        $userDepositMinings = $colony->getUserDepositMinings();

        $this->mainLoop($colony, $userDepositMinings);

        $this->colonyRepository->save($colony);

        $this->proceedModules($colony);
        $this->sendMessages($colony);

        if ($doLog) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("Colony-Id: %6d, seconds: %F", $colony->getId(), $endTime - $startTime));
        }
    }

    /**
     * @param ColonyDepositMiningInterface[] $userDepositMinings
     */
    private function mainLoop(ColonyInterface $colony, array $userDepositMinings)
    {
        $doLog = $this->loggerUtil->doLog();

        if ($doLog) {
            $startTime = microtime(true);
        }

        $i = 1;
        $storage = $colony->getStorage();

        $production = $this->colonyLibFactory->createColonyCommodityProduction($colony)->getProduction();

        while (true) {
            $rewind = 0;
            foreach ($production as $commodityId => $pro) {
                if ($pro->getProduction() >= 0) {
                    continue;
                }

                $depositMining = $userDepositMinings[$commodityId] ?? null;
                if ($depositMining !== null) {
                    if ($depositMining->isEnoughLeft((int) abs($pro->getProduction()))) {
                        continue;
                    }
                }

                $storageItem = $storage[$pro->getCommodityId()] ?? null;
                if ($storageItem !== null && $storageItem->getAmount() + $pro->getProduction() >= 0) {
                    continue;
                }
                //echo "coloId:" . $colony->getId() . ", production:" . $pro->getProduction() . ", commodityId:" . $commodityId . ", commodity:" . $this->commodityArray[$commodityId]->getName() . "\n";
                $field = $this->getBuildingToDeactivateByCommodity($colony, $commodityId);
                $name = '';
                // echo $i." hit by commodity ".$field->getFieldId()." - produce ".$pro->getProduction()." MT ".microtime()."\n";
                $this->deactivateBuilding($field, $production, $this->commodityArray[$commodityId], $name);
                $rewind = 1;
            }

            if ($rewind == 0 && $colony->getWorkers() > $colony->getMaxBev()) {
                $field = $this->getBuildingToDeactivateByLivingSpace($colony);
                $name = 'Wohnraum';
                $this->deactivateBuilding($field, $production, null, $name);
                $rewind = 1;
            }

            $energyProduction = $this->planetFieldRepository->getEnergyProductionByColony($colony->getId());

            if ($rewind == 0 && $energyProduction < 0 && $colony->getEps() + $energyProduction < 0) {
                $field = $this->getBuildingToDeactivateByEpsUsage($colony);
                $name = 'Energie';
                //echo $i . " hit by eps " . $field->getFieldId() . " - complete usage " . $colony->getEpsProduction() . " - usage " . $field->getBuilding()->getEpsProduction() . " MT " . microtime() . "\n";
                $this->deactivateBuilding($field, $production, null, $name);
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
        $colony->setEps(
            min(
                $colony->getMaxEps(),
                $colony->getEps() + $this->planetFieldRepository->getEnergyProductionByColony($colony->getId())
            )
        );

        if ($doLog) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tmainLoop, seconds: %F", $endTime - $startTime));
        }

        $this->proceedStorage($colony, $userDepositMinings, $production);
    }

    /**
     * @param array<ColonyProduction> $production
     */
    private function deactivateBuilding(
        PlanetFieldInterface $field,
        array &$production,
        CommodityInterface $commodity = null,
        string $name
    ): void {
        if ($name != '') {
            $ext = $name;
        } else {
            $ext = $commodity->getName();
        }
        $building = $field->getBuilding();

        $this->buildingManager->deactivate($field);
        $this->entityManager->flush();

        $this->mergeProduction($building->getCommodities(), $production);

        $this->msg[] = $building->getName() . " auf Feld " . $field->getFieldId() . " deaktiviert (Mangel an " . $ext . ")";
    }

    private function getBuildingToDeactivateByCommodity(ColonyInterface $colony, int $commodityId): PlanetFieldInterface
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

    private function getBuildingToDeactivateByLivingSpace(ColonyInterface $colony): PlanetFieldInterface
    {
        $fields = $this->planetFieldRepository->getWorkerConsumingByColonyAndState($colony->getId(), [1], 1);

        return current($fields);
    }

    /**
     * @param ColonyDepositMiningInterface[] $userDepositMinings
     * @param array<ColonyProduction> $production
     */
    private function proceedStorage(
        ColonyInterface $colony,
        array $userDepositMinings,
        array $production
    ): void {
        $doLog = $this->loggerUtil->doLog();
        if ($doLog) {
            $startTime = microtime(true);
        }

        $sum = $colony->getStorageSum();

        if ($doLog) {
            $startTime = microtime(true);
        }

        //DECREASE
        foreach ($production as $commodityId => $obj) {
            $amount = $obj->getProduction();
            $commodity = $this->commodityArray[$commodityId];

            if ($amount < 0) {
                $amount = (int) abs($amount);

                if ($commodity->isSaveable()) {
                    // STANDARD
                    $this->colonyStorageManager->lowerStorage(
                        $colony,
                        $this->commodityArray[$commodityId],
                        $amount
                    );
                    $sum -= $amount;
                } else {
                    // EFFECTS
                    $depositMining = $userDepositMinings[$commodityId];

                    $depositMining->setAmountLeft($depositMining->getAmountLeft() - $amount);
                    $this->colonyDepositMiningRepository->save($depositMining);
                }
            }
        }
        if ($doLog) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tforeach1, seconds: %F", $endTime - $startTime));
        }

        if ($doLog) {
            $startTime = microtime(true);
        }
        foreach ($production as $commodityId => $obj) {
            if ($doLog) {
                $startTimeC = microtime(true);
            }

            $commodity = $this->commodityArray[$commodityId];
            if ($obj->getProduction() <= 0 || !$commodity->isSaveable()) {
                continue;
            }
            if ($sum >= $colony->getMaxStorage()) {
                if ($colony->getUser()->isStorageNotification()) {
                    $this->msg[] = _('Das Lager der Kolonie ist voll');
                }
                break;
            }
            if ($sum + $obj->getProduction() > $colony->getMaxStorage()) {
                $this->colonyStorageManager->upperStorage(
                    $colony,
                    $commodity,
                    $colony->getMaxStorage() - $sum
                );
                if ($colony->getUser()->isStorageNotification()) {
                    $this->msg[] = _('Das Lager der Kolonie ist voll');
                }
                break;
            }
            if ($doLog) {
                $startTimeM = microtime(true);
            }
            $this->colonyStorageManager->upperStorage(
                $colony,
                $commodity,
                $obj->getProduction()
            );
            if ($doLog) {
                $endTimeM = microtime(true);
                $this->loggerUtil->log(sprintf("\t\t\tupper, seconds: %F", $endTimeM - $startTimeM));
            }
            $sum += $obj->getProduction();
            if ($doLog) {
                $endTimeC = microtime(true);
                $this->loggerUtil->log(sprintf("\t\tcommodity: %s, seconds: %F", $commodity->getName(), $endTimeC - $startTimeC));
            }
        }
        if ($doLog) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tforeach2, seconds: %F", $endTime - $startTime));
        }

        if ($doLog) {
            $startTime = microtime(true);
        }
        $current_research = $this->researchedRepository->getCurrentResearch($colony->getUser());

        if ($current_research && $current_research->getActive()) {
            if (isset($production[$current_research->getResearch()->getCommodityId()])) {
                (new ResearchState(
                    $this->researchedRepository,
                    $this->shipRumpUserRepository,
                    $this->privateMessageSender,
                    $this->createDatabaseEntry,
                    $this->crewCreator,
                    $this->shipCreator,
                    $this->shipRepository,
                    $this->shipSystemManager,
                    $this->createUserAward,
                    $this->entityManager,
                ))->advance(
                    $current_research,
                    $production[$current_research->getResearch()->getCommodityId()]->getProduction()
                );
            }
        }
        if ($doLog) {
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
        $this->proceedImmigration(
            $colony,
            $production
        );

        if ($doLog) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tstorage, seconds: %F", $endTime - $startTime));
        }
    }

    private function proceedModules(ColonyInterface $colony): void
    {
        foreach ($this->moduleQueueRepository->getByColony((int) $colony->getId()) as $queue) {
            $buildingFunction = $queue->getBuildingFunction();

            //spare parts and system components are generated by ship tick, to avoid dead locks
            if (
                $buildingFunction === BuildingEnum::BUILDING_FUNCTION_FABRICATION_HALL ||
                $buildingFunction === BuildingEnum::BUILDING_FUNCTION_TECH_CENTER
            ) {
                continue;
            }

            if ($this->colonyFunctionManager->hasActiveFunction($colony, $buildingFunction, false)) {
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
     * @param array<int, ColonyProduction> $production
     */
    private function proceedImmigration(
        ColonyInterface $colony,
        array $production
    ): void {
        // @todo
        $colony->setWorkless(
            $colony->getWorkless() +
                $this->colonyLibFactory->createColonyPopulationCalculator($colony, $production)->getGrowth()
        );
    }

    private function proceedEmigration(ColonyInterface $colony)
    {
        if ($colony->getWorkless()) {
            $bev = rand(1, $colony->getWorkless());
            $colony->setWorkless($colony->getWorkless() - $bev);
            $this->msg[] = $bev . " Einwohner sind ausgewandert";
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
            UserEnum::USER_NOONE,
            (int) $colony->getUserId(),
            $text,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
            $href
        );

        $this->msg = [];
    }

    /**
     * @param Collection<int, BuildingCommodityInterface> $buildingProduction
     * @param array<ColonyProduction> $production
     */
    private function mergeProduction(
        Collection $buildingProduction,
        array &$production
    ): void {
        foreach ($buildingProduction as $obj) {
            $commodityId = $obj->getCommodityId();
            if (!array_key_exists($commodityId, $production)) {
                $data = $this->colonyLibFactory->createColonyProduction(
                    $obj->getCommodity(),
                    $obj->getAmount() * -1
                );

                $production[$commodityId] = $data;
            } else {
                if ($obj->getAmount() < 0) {
                    $production[$commodityId]->upperProduction(abs($obj->getAmount()));
                } else {
                    $production[$commodityId]->lowerProduction($obj->getAmount());
                }
            }
        }
    }
}
