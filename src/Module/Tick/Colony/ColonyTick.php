<?php

namespace Stu\Module\Tick\Colony;

use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use RuntimeException;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\Information\InformationFactoryInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Tick\Colony\Component\ColonyTickComponentInterface;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\ColonyDepositMiningRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonyTick implements ColonyTickInterface
{
    private InformationWrapper $information;

    /** @param array<ColonyTickComponentInterface> $components */
    public function __construct(
        private readonly ModuleQueueRepositoryInterface $moduleQueueRepository,
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly ColonyRepositoryInterface $colonyRepository,
        private readonly ColonyDepositMiningRepositoryInterface $colonyDepositMiningRepository,
        private readonly PrivateMessageSenderInterface $privateMessageSender,
        private readonly StorageManagerInterface $storageManager,
        private readonly BuildingManagerInterface $buildingManager,
        private readonly ColonyLibFactoryInterface $colonyLibFactory,
        private readonly ColonyFunctionManagerInterface $colonyFunctionManager,
        private readonly CommodityCacheInterface $commodityCache,
        private readonly InformationFactoryInterface $informationFactory,
        private readonly array $components
    ) {}

    #[\Override]
    public function work(Colony $colony): void
    {
        $this->information = $this->informationFactory->createInformationWrapper();

        $deactivatedFields = $this->mainLoop($colony);

        $this->colonyRepository->save($colony);

        $this->proceedModules($colony, $deactivatedFields);
        $this->sendMessages($colony);
    }

    /**
     * @return array<int>
     */
    private function mainLoop(Colony $colony): array
    {
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
        $changeable = $colony->getChangeable();
        $changeable->setEps(
            min(
                $colony->getMaxEps(),
                $changeable->getEps() + $this->planetFieldRepository->getEnergyProductionByHost($colony, $deactivatedFields)
            )
        );

        foreach ($this->components as $component) {
            $component->work($colony, $production, $this->information);
        }

        return $deactivatedFields;
    }

    /**
     * @param array<int, ColonyProduction> $production
     * @param array<int> $deactivatedFields
     */
    private function checkStorage(
        Colony $colony,
        array &$production,
        array &$deactivatedFields
    ): bool {

        $result = false;

        foreach ($production as $pro) {
            if ($pro->getProduction() >= 0) {
                continue;
            }

            $commodityId = $pro->getCommodityId();

            $depositMining = $this->colonyDepositMiningRepository->getCurrentUserDepositMinings($colony)[$commodityId] ?? null;
            if ($depositMining !== null && $depositMining->isEnoughLeft(abs($pro->getProduction()))) {
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
        Colony $colony,
        array &$production,
        array &$deactivatedFields
    ): bool {
        if ($colony->getWorkers() > $colony->getChangeable()->getMaxBev()) {
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
        Colony $colony,
        array &$production,
        array &$deactivatedFields
    ): bool {
        $energyProduction = $this->planetFieldRepository->getEnergyProductionByHost($colony, $deactivatedFields);

        if ($energyProduction < 0 && $colony->getChangeable()->getEps() + $energyProduction < 0) {
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
        PlanetField $field,
        array &$production,
        Commodity|string $cause
    ): void {
        $ext = $cause instanceof Commodity ? $cause->getName() : $cause;
        $building = $field->getBuilding();

        if ($building === null) {
            throw new InvalidArgumentException('can not deactivate field without building');
        }

        $this->buildingManager->deactivate($field);

        $this->mergeProduction($building->getCommodities(), $production);

        $this->information->addInformationf(
            "%s auf Feld %d deaktiviert (Mangel an %s)",
            $building->getName(),
            $field->getFieldId(),
            $ext
        );
    }

    /**
     * @param array<int> $deactivatedFields
     */
    private function getBuildingToDeactivateByCommodity(
        Colony $colony,
        int $commodityId,
        array $deactivatedFields
    ): PlanetField {
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
        Colony $colony,
        array $deactivatedFields
    ): PlanetField {
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
        Colony $colony,
        array $deactivatedFields
    ): ?PlanetField {
        $fields = $this->planetFieldRepository->getWorkerConsumingByColonyAndState(
            $colony->getId(),
            [1],
            1,
            $deactivatedFields
        );

        return $fields === [] ? null : current($fields);
    }

    /**
     * @param array<int> $deactivatedFields
     */
    private function proceedModules(Colony $colony, array $deactivatedFields): void
    {
        foreach ($this->moduleQueueRepository->getByColony($colony->getId()) as $queue) {
            $buildingFunction = $queue->getBuildingFunction();

            //spare parts and system components are generated by spacecraft tick manager, to avoid dead locks
            if (
                $buildingFunction === BuildingFunctionEnum::FABRICATION_HALL ||
                $buildingFunction === BuildingFunctionEnum::TECH_CENTER
            ) {
                continue;
            }

            if ($this->colonyFunctionManager->hasActiveFunction($colony, $buildingFunction, false, $deactivatedFields)) {
                $this->storageManager->upperStorage(
                    $colony,
                    $queue->getModule()->getCommodity(),
                    $queue->getAmount()
                );

                $this->information->addInformationf(
                    _('Es wurden %d %s hergestellt'),
                    $queue->getAmount(),
                    $queue->getModule()->getName()
                );
                $this->moduleQueueRepository->delete($queue);
            }
        }
    }

    private function sendMessages(Colony $colony): void
    {
        if ($this->information->isEmpty()) {
            return;
        }

        $text = sprintf(
            "Tickreport der Kolonie %s\n%s",
            $colony->getName(),
            $this->information->getInformationsAsString()
        );

        $this->privateMessageSender->send(
            UserConstants::USER_NOONE,
            $colony->getUserId(),
            $text,
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY,
            $colony
        );

        $this->information = $this->informationFactory->createInformationWrapper();
    }

    /**
     * @param Collection<int, BuildingCommodity> $buildingProduction
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
