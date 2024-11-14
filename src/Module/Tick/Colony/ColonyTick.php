<?php

namespace Stu\Module\Tick\Colony;

use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Override;
use RuntimeException;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Tick\Colony\Component\ColonyTickComponentInterface;
use Stu\Orm\Entity\BuildingCommodityInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyDepositMiningRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonyTick implements ColonyTickInterface
{
    private LoggerUtilInterface $loggerUtil;

    /**
     * @var array<string>
     */
    private array $msg = [];

    /** @param array<ColonyTickComponentInterface> $components */
    public function __construct(
        private ModuleQueueRepositoryInterface $moduleQueueRepository,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private ColonyStorageManagerInterface $colonyStorageManager,
        private ColonyRepositoryInterface $colonyRepository,
        private BuildingManagerInterface $buildingManager,
        private ColonyDepositMiningRepositoryInterface $colonyDepositMiningRepository,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        private CommodityCacheInterface $commodityCache,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        private array $components
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function work(ColonyInterface $colony): void
    {
        $doLog = $this->loggerUtil->doLog();
        if ($doLog) {
            $startTime = microtime(true);
        }

        $this->mainLoop($colony);

        $this->colonyRepository->save($colony);

        $this->proceedModules($colony);
        $this->sendMessages($colony);

        if ($doLog) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("Colony-Id: %6d, seconds: %F", $colony->getId(), $endTime - $startTime));
        }
    }

    private function mainLoop(ColonyInterface $colony): void
    {
        $doLog = $this->loggerUtil->doLog();

        if ($doLog) {
            $startTime = microtime(true);
        }

        $i = 1;
        $production = $this->colonyLibFactory->createColonyCommodityProduction($colony)->getProduction();

        $deactivatedFields = [-1];

        while (true) {

            $rewind = $this->checkStorage($colony, $production, $deactivatedFields);
            $rewind |= $this->checkLivingSpace($colony, $production, $deactivatedFields);
            $rewind |= $this->checkEnergyProduction($colony, $production, $deactivatedFields);

            if ($rewind !== 0) {
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
                $colony->getEps() + $this->planetFieldRepository->getEnergyProductionByHost($colony, $deactivatedFields)
            )
        );

        if ($doLog) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tmainLoop, seconds: %F", $endTime - $startTime));
        }

        foreach ($this->components as $component) {
            $component->work($colony, $production);
        }

        $this->proceedStorage($colony, $production);
    }

    /**
     * @param array<int, ColonyProduction> $production
     * @param array<int> $deactivatedFields
     */
    private function checkStorage(
        ColonyInterface $colony,
        array &$production,
        array &$deactivatedFields
    ): bool {

        $result = false;

        foreach ($production as $pro) {
            if ($pro->getProduction() >= 0) {
                continue;
            }

            $commodityId = $pro->getCommodityId();

            $depositMining = $colony->getUserDepositMinings()[$commodityId] ?? null;
            if ($depositMining !== null && $depositMining->isEnoughLeft((int) abs($pro->getProduction()))) {
                continue;
            }

            $storage = $colony->getStorage();
            $storageItem = $storage[$commodityId] ?? null;
            if ($storageItem !== null && $storageItem->getAmount() + $pro->getProduction() >= 0) {
                continue;
            }
            //echo "coloId:" . $colony->getId() . ", production:" . $pro->getProduction() . ", commodityId:" . $commodityId . ", commodity:" . $this->commodityCache->get($commodityId)->getName() . "\n";
            $field = $this->getBuildingToDeactivateByCommodity($colony, $commodityId, $deactivatedFields);
            // echo $i." hit by commodity ".$field->getFieldId()." - produce ".$pro->getProduction()." MT ".microtime()."\n";
            $this->deactivateBuilding($field, $production, $this->commodityCache->get($commodityId));
            $deactivatedFields[] = $field->getFieldId();

            $result = true;
        }

        return $result;
    }

    /**
     * @param array<int, ColonyProduction> $production
     * @param array<int> $deactivatedFields
     */
    private function checkLivingSpace(
        ColonyInterface $colony,
        array &$production,
        array &$deactivatedFields
    ): bool {
        if ($colony->getWorkers() > $colony->getMaxBev()) {
            $field = $this->getBuildingToDeactivateByLivingSpace($colony, $deactivatedFields);
            if ($field !== null) {
                $this->deactivateBuilding($field, $production, 'Wohnraum');
                $deactivatedFields[] = $field->getFieldId();

                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, ColonyProduction> $production
     * @param array<int> $deactivatedFields
     */
    private function checkEnergyProduction(
        ColonyInterface $colony,
        array &$production,
        array &$deactivatedFields
    ): bool {
        $energyProduction = $this->planetFieldRepository->getEnergyProductionByHost($colony, $deactivatedFields);

        if ($energyProduction < 0 && $colony->getEps() + $energyProduction < 0) {
            $field = $this->getBuildingToDeactivateByEpsUsage($colony, $deactivatedFields);
            //echo $i . " hit by eps " . $field->getFieldId() . " - complete usage " . $colony->getEpsProduction() . " - usage " . $field->getBuilding()->getEpsProduction() . " MT " . microtime() . "\n";
            $this->deactivateBuilding($field, $production, 'Energie');
            $deactivatedFields[] = $field->getFieldId();

            return true;
        }

        return false;
    }

    /**
     * @param array<int, ColonyProduction> $production
     */
    private function deactivateBuilding(
        PlanetFieldInterface $field,
        array &$production,
        CommodityInterface|string $cause
    ): void {
        $ext = $cause instanceof CommodityInterface ? $cause->getName() : $cause;
        $building = $field->getBuilding();

        if ($building === null) {
            throw new InvalidArgumentException('can not deactivate field without building');
        }

        $this->buildingManager->deactivate($field);

        $this->mergeProduction($building->getCommodities(), $production);

        $this->msg[] = $building->getName() . " auf Feld " . $field->getFieldId() . " deaktiviert (Mangel an " . $ext . ")";
    }

    /**
     * @param array<int> $deactivatedFields
     */
    private function getBuildingToDeactivateByCommodity(
        ColonyInterface $colony,
        int $commodityId,
        array $deactivatedFields
    ): PlanetFieldInterface {
        $fields = $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
            $colony,
            $commodityId,
            [1],
            1,
            $deactivatedFields
        );

        $result = current($fields);
        if (!$result) {
            throw new RuntimeException('no building found');
        }

        return $result;
    }

    /**
     * @param array<int> $deactivatedFields
     */
    private function getBuildingToDeactivateByEpsUsage(
        ColonyInterface $colony,
        array $deactivatedFields
    ): PlanetFieldInterface {
        $fields = $this->planetFieldRepository->getEnergyConsumingByHost(
            $colony,
            [1],
            1,
            $deactivatedFields
        );

        $result = current($fields);
        if (!$result) {
            throw new RuntimeException('no building found');
        }

        return $result;
    }

    /**
     * @param array<int> $deactivatedFields
     */
    private function getBuildingToDeactivateByLivingSpace(
        ColonyInterface $colony,
        array $deactivatedFields
    ): ?PlanetFieldInterface {
        $fields = $this->planetFieldRepository->getWorkerConsumingByColonyAndState(
            $colony->getId(),
            [1],
            1,
            $deactivatedFields
        );

        return $fields === [] ? null : current($fields);
    }

    /**
     * @param array<ColonyProduction> $production
     */
    private function proceedStorage(
        ColonyInterface $colony,
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
            $commodity = $this->commodityCache->get($commodityId);

            if ($amount < 0) {
                $amount = abs($amount);

                if ($commodity->isSaveable()) {
                    // STANDARD
                    $this->colonyStorageManager->lowerStorage(
                        $colony,
                        $this->commodityCache->get($commodityId),
                        $amount
                    );
                    $sum -= $amount;
                } else {
                    // EFFECTS
                    $depositMining = $colony->getUserDepositMinings()[$commodityId];

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
            $startTimeC = microtime(true);

            $commodity = $this->commodityCache->get($commodityId);
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
            $startTimeM = microtime(true);
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
        foreach ($this->moduleQueueRepository->getByColony($colony->getId()) as $queue) {
            $buildingFunction = $queue->getBuildingFunction();

            //spare parts and system components are generated by ship tick manager, to avoid dead locks
            if (
                $buildingFunction === BuildingFunctionEnum::BUILDING_FUNCTION_FABRICATION_HALL ||
                $buildingFunction === BuildingFunctionEnum::BUILDING_FUNCTION_TECH_CENTER
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

    private function proceedEmigration(ColonyInterface $colony): void
    {
        if ($colony->getWorkless() !== 0) {
            $bev = random_int(1, $colony->getWorkless());
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

        $href = sprintf(_('colony.php?%s=1&id=%d'), ShowColony::VIEW_IDENTIFIER, $colony->getId());

        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $colony->getUserId(),
            $text,
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY,
            $href
        );

        $this->msg = [];
    }

    /**
     * @param Collection<int, BuildingCommodityInterface> $buildingProduction
     * @param array<int, ColonyProduction> $production
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
            } elseif ($obj->getAmount() < 0) {
                $production[$commodityId]->upperProduction(abs($obj->getAmount()));
            } else {
                $production[$commodityId]->lowerProduction($obj->getAmount());
            }
        }
    }
}
