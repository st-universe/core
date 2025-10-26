<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\LocationMiningRepositoryInterface;

class BussardCollectorHandler implements SpacecraftTickHandlerInterface
{
    public function __construct(
        private readonly LocationMiningRepositoryInterface $locationMiningRepository,
        private readonly StorageManagerInterface $storageManager,
        private readonly StuRandom $stuRandom
    ) {}

    #[\Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {

        if (!$wrapper instanceof ShipWrapperInterface) {
            return;
        }

        $ship = $wrapper->get();
        $bussard = $wrapper->getBussardCollectorSystemData();
        $miningqueue = $ship->getMiningQueue();

        if ($bussard === null) {
            return;
        }

        if ($miningqueue == null) {
            return;
        } else {
            $locationmining = $miningqueue->getLocationMining();
            $actualAmount = $locationmining->getActualAmount();
            $freeStorage = $ship->getMaxStorage() - $ship->getStorageSum();
            $module = $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR)->getModule();
            $gathercount = 0;

            if ($module !== null) {
                if ($module->getFactionId() == null) {
                    $gathercount =  (int) min(min(round($this->stuRandom->rand(95, 105)), $actualAmount), $freeStorage);
                } else {
                    $gathercount = (int) min(min(round($this->stuRandom->rand(190, 220)), $actualAmount), $freeStorage);
                }
            }

            $newAmount = $actualAmount - $gathercount;
            if ($gathercount > 0 && $locationmining->getDepletedAt() !== null) {
                $locationmining->setDepletedAt(null);
            }
            if ($newAmount == 0 && $actualAmount > 0) {
                $locationmining->setDepletedAt(time());
                $information->addInformationf(
                    'Es sind keine %s bei den Koordinaten %s|%s vorhanden!',
                    $locationmining->getCommodity()->getName(),
                    (string)$locationmining->getLocation()->getCx(),
                    (string)$locationmining->getLocation()->getCy()
                );
            }
            $locationmining->setActualAmount($newAmount);

            $this->locationMiningRepository->save($locationmining);
            if ($gathercount + $ship->getStorageSum() >= $ship->getMaxStorage()) {
                $information->addInformationf('Der Lagerraum des Schiffes wurde beim Sammeln von %s voll!', $locationmining->getCommodity()->getName());
            }

            if ($gathercount > 0) {
                $this->storageManager->upperStorage(
                    $ship,
                    $locationmining->getCommodity(),
                    $gathercount
                );
            }
        }
    }
}
