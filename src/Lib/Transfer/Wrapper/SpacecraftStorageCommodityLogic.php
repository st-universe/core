<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Doctrine\Common\Collections\Collection;
use request;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Storage;

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
            && $spacecraft instanceof Ship
            && $targetEntity instanceof Ship
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

        $spacecraft = $wrapper->get();
        $epsSystem = $wrapper->getEpsSystemData();

        $transferTarget = $isUnload ? $target->get() : $spacecraft;
        $storage = $isUnload ? $spacecraft->getBeamableStorage() : $target->get()->getBeamableStorage();

        if ($this->isSanityFaulty($spacecraft, $transferTarget, $storage, $epsSystem, $information)) {
            return false;
        }

        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');

        if (count($commodities) == 0 || count($gcount) == 0) {
            $information->addInformation("Es wurden keine Waren zum Beamen ausgewählt");
            return false;
        }

        $information->addInformationf(
            'Die %s hat folgende Waren %s %s %s transferiert',
            $spacecraft->getName(),
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
                $isUnload ? $spacecraft : $target->get(),
                $transferTarget,
                $information
            )) {
                $hasTransfered = true;
            }
        }

        return $hasTransfered;
    }

    /** @param Collection<int, Storage> $storage */
    private function isSanityFaulty(
        Spacecraft $spacecraft,
        EntityWithStorageInterface $transferTarget,
        Collection $storage,
        ?EpsSystemData $epsSystem,
        InformationInterface $information
    ): bool {

        $isDockTransfer = $this->commodityTransfer->isDockTransfer($spacecraft, $transferTarget);

        if (!$isDockTransfer && ($epsSystem === null || $epsSystem->getEps() === 0)) {
            $information->addInformation("Keine Energie vorhanden");
            return true;
        }
        if ($spacecraft->isCloaked()) {
            $information->addInformation("Die Tarnung ist aktiviert");
            return true;
        }
        if ($spacecraft->isWarped()) {
            $information->addInformation("Schiff befindet sich im Warp");
            return true;
        }

        if ($transferTarget->getMaxStorage() <= $transferTarget->getStorageSum()) {
            $information->addInformationf('%s: Der Lagerraum ist voll', $transferTarget->getName());
            return true;
        }

        if ($storage->isEmpty()) {
            $information->addInformation("Keine Waren zum Beamen vorhanden");
            return true;
        }

        return false;
    }
}
