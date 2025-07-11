<?php

namespace Stu\Module\Tick\Colony\Component;

use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\ColonyDepositMiningRepositoryInterface;

class ProceedStorage implements ColonyTickComponentInterface
{
    public function __construct(
        private readonly ColonyDepositMiningRepositoryInterface $colonyDepositMiningRepository,
        private readonly CommodityCacheInterface $commodityCache,
        private readonly StorageManagerInterface $storageManager,
        private readonly UserSettingsProviderInterface $userSettingsProvider
    ) {}

    public function work(Colony $colony, array &$production, InformationInterface $information): void
    {
        $sum = $colony->getStorageSum();

        //DECREASE
        foreach ($production as $commodityId => $obj) {
            $amount = $obj->getProduction();
            $commodity = $this->commodityCache->get($commodityId);

            if ($amount < 0) {
                $amount = abs($amount);

                if ($commodity->isSaveable()) {
                    // STANDARD
                    $this->storageManager->lowerStorage(
                        $colony,
                        $this->commodityCache->get($commodityId),
                        $amount
                    );
                    $sum -= $amount;
                } else {
                    // EFFECTS
                    $depositMining = $this->colonyDepositMiningRepository->getCurrentUserDepositMinings($colony)[$commodityId];

                    $depositMining->setAmountLeft($depositMining->getAmountLeft() - $amount);
                    $this->colonyDepositMiningRepository->save($depositMining);
                }
            }
        }

        foreach ($production as $commodityId => $obj) {

            $commodity = $this->commodityCache->get($commodityId);
            if ($obj->getProduction() <= 0 || !$commodity->isSaveable()) {
                continue;
            }

            $isStorageNotification = $this->userSettingsProvider->isStorageNotification($colony->getUser());
            if ($sum >= $colony->getMaxStorage()) {
                if ($isStorageNotification) {
                    $information->addInformation('Das Lager der Kolonie ist voll');
                }
                break;
            }
            if ($sum + $obj->getProduction() > $colony->getMaxStorage()) {
                $this->storageManager->upperStorage(
                    $colony,
                    $commodity,
                    $colony->getMaxStorage() - $sum
                );
                if ($isStorageNotification) {
                    $information->addInformation('Das Lager der Kolonie ist voll');
                }
                break;
            }
            $this->storageManager->upperStorage(
                $colony,
                $commodity,
                $obj->getProduction()
            );
            $sum += $obj->getProduction();
        }
    }
}
