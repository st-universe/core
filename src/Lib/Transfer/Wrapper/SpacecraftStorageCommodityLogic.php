<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use request;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class SpacecraftStorageCommodityLogic
{
    public function __construct(
        private PirateReactionInterface $pirateReaction,
        private CommodityTransferInterface $commodityTransfer
    ) {}

    public function transfer(
        bool $isUnload,
        SpacecraftWrapperInterface $wrapper,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {

        $hasTransfered = false;

        // check for fleet option
        $fleetWrapper = $wrapper->getFleetWrapper();
        if (request::postInt('isfleet') && $fleetWrapper !== null) {
            foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
                if ($this->transferPerSpacecraft(
                    $isUnload,
                    $wrapper,
                    $target,
                    $information
                )) {
                    $hasTransfered = true;
                }
            }
        } else {
            $hasTransfered =  $this->transferPerSpacecraft($isUnload, $wrapper, $target, $information);
        }

        $spacecraft = $wrapper->get();
        $targetEntity = $target->get();
        if (
            !$isUnload
            && $hasTransfered
            && $spacecraft instanceof ShipInterface
            && $targetEntity instanceof ShipInterface
        ) {
            $this->pirateReaction->checkForPirateReaction(
                $targetEntity,
                PirateReactionTriggerEnum::ON_BEAM,
                $spacecraft
            );
        }
    }

    private function transferPerSpacecraft(
        bool $isUnload,
        SpacecraftWrapperInterface $wrapper,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): bool {

        $ship = $wrapper->get();
        $epsSystem = $wrapper->getEpsSystemData();

        //sanity checks
        $isDockTransfer = $this->commodityTransfer->isDockTransfer($ship, $target->get());
        if (!$isDockTransfer && ($epsSystem === null || $epsSystem->getEps() === 0)) {
            $information->addInformation("Keine Energie vorhanden");
            return false;
        }
        if ($ship->isCloaked()) {
            $information->addInformation("Die Tarnung ist aktiviert");
            return false;
        }
        if ($ship->isWarped()) {
            $information->addInformation("Schiff befindet sich im Warp");
            return false;
        }

        $transferTarget = $isUnload ? $target->get() : $ship;
        if ($transferTarget->getMaxStorage() <= $transferTarget->getStorageSum()) {
            $information->addInformationf('%s: Der Lagerraum ist voll', $isUnload ? $target->getName() : $ship->getName());
            return false;
        }

        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');

        $storage = $isUnload ? $ship->getBeamableStorage() : $target->get()->getBeamableStorage();

        if ($storage->isEmpty()) {
            $information->addInformation("Keine Waren zum Beamen vorhanden");
            return false;
        }
        if (count($commodities) == 0 || count($gcount) == 0) {
            $information->addInformation("Es wurden keine Waren zum Beamen ausgewählt");
            return false;
        }
        $information->addInformationf(
            'Die %s hat folgende Waren %s %s %s transferiert',
            $ship->getName(),
            $isUnload ? 'zur' : 'von der',
            $target->get()->getTransferEntityType()->getName(),
            $target->getName()
        );

        $hasTransfered = false;
        foreach ($commodities as $key => $value) {
            $commodityId = (int) $value;

            if (!array_key_exists($key, $gcount)) {
                continue;
            }

            if ($this->commodityTransfer->transferCommodity(
                $commodityId,
                $gcount[$key],
                $wrapper,
                $isUnload ? $ship : $target->get(),
                $transferTarget,
                $information
            )) {
                $hasTransfered = true;
            }
        }

        return $hasTransfered;
    }
}
