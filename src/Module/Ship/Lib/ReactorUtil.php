<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ReactorUtil implements ReactorUtilInterface
{
    private ShipStorageManagerInterface $shipStorageManager;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ShipRepositoryInterface $shipRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipStorageManagerInterface $shipStorageManager,
        ColonyStorageManagerInterface $colonyStorageManager,
        ShipRepositoryInterface $shipRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipStorageManager = $shipStorageManager;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->shipRepository = $shipRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function storageContainsNeededCommodities($storage, bool $isWarpcore = true): bool
    {
        $costs = $isWarpcore ? ShipEnum::WARPCORE_LOAD_COST : ShipEnum::REACTOR_LOAD_COST;

        foreach ($costs as $commodityId => $loadCost) {
            if (!$storage->containsKey($commodityId)) {
                return false;
            }
            if ($storage->get($commodityId)->getAmount() < $loadCost) {
                return false;
            }
        }

        return true;
    }

    public function loadReactor(
        ShipInterface $ship,
        int $additionalLoad,
        ?ColonyInterface $colony = null,
        ?ShipInterface $station = null,
        bool $isWarpcore = true
    ): ?string {
        if ($ship->getReactorLoad() >= $ship->getReactorCapacity()) {
            return null;
        }

        $loadUnits = $isWarpcore ? ShipEnum::WARPCORE_LOAD : ShipEnum::REACTOR_LOAD;

        //check for core limitation
        $loadUnits = ceil($additionalLoad / $loadUnits);
        if ($loadUnits * $loadUnits > $ship->getReactorCapacity() - $ship->getReactorLoad()) {
            $loadUnits = ceil(($ship->getReactorCapacity() - $ship->getReactorLoad()) / $loadUnits);
        }

        $loadUnits = (int) $loadUnits;

        if ($loadUnits < 1) {
            return null;
        }

        if ($colony !== null) {
            $storage = $colony->getStorage();
        } else if ($station !== null) {
            $storage = $station->getStorage();
        } else {
            $storage = $ship->getStorage();
        }

        // check for ressource limitation
        $costs = $isWarpcore ? ShipEnum::WARPCORE_LOAD_COST : ShipEnum::REACTOR_LOAD_COST;
        foreach ($costs as $commodityId => $loadUnitsCost) {
            if ($storage[$commodityId]->getAmount() < ($loadUnits * $loadUnitsCost)) {
                $loadUnits = (int) ($storage[$commodityId]->getAmount() / $loadUnitsCost);
            }
        }

        //consume ressources
        foreach ($costs as $commodityId => $loadCost) {
            if ($colony !== null) {
                $this->colonyStorageManager->lowerStorage(
                    $colony,
                    $storage[$commodityId]->getCommodity(),
                    $loadCost * $loadUnits
                );
            } else if ($station !== null) {
                $this->shipStorageManager->lowerStorage(
                    $station,
                    $storage[$commodityId]->getCommodity(),
                    $loadCost * $loadUnits
                );
            } else {
                $this->shipStorageManager->lowerStorage(
                    $ship,
                    $storage[$commodityId]->getCommodity(),
                    $loadCost * $loadUnits
                );
            }
        }

        //truncate output
        if ($ship->getReactorLoad() + $loadUnits * $loadUnits > $ship->getReactorCapacity()) {
            $loadUnits = $ship->getReactorCapacity() - $ship->getReactorLoad();
        } else {
            $loadUnits = $loadUnits * $loadUnits;
        }
        $ship->setReactorLoad($ship->getReactorLoad() + $loadUnits);
        $this->shipRepository->save($ship);

        if ($colony !== null) {

            $this->privateMessageSender->send(
                $colony->getUser()->getId(),
                $ship->getUser()->getId(),
                sprintf(
                    _('Die Kolonie %s hat in Sektor %s den %s der %s um %d Einheiten aufgeladen'),
                    $colony->getName(),
                    $ship->getSectorString(),
                    $isWarpcore ? 'Warpkern' : 'Fusionsreaktor',
                    $ship->getName(),
                    $loadUnits
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
                sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $ship->getId())
            );
        } else if ($station !== null) {

            $this->privateMessageSender->send(
                $station->getUser()->getId(),
                $ship->getUser()->getId(),
                sprintf(
                    _('Die %s %s hat in Sektor %s den %s der %s um %d Einheiten aufgeladen'),
                    $station->getRump()->getName(),
                    $station->getName(),
                    $ship->getSectorString(),
                    $isWarpcore ? 'Warpkern' : 'Fusionsreaktor',
                    $ship->getName(),
                    $loadUnits
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
                sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $ship->getId())
            );
        }

        return sprintf(
            _('%s: Der %s wurde um %d Einheiten aufgeladen'),
            $ship->getName(),
            $isWarpcore ? 'Warpkern' : 'Fusionsreaktor',
            $loadUnits
        );
    }
}
