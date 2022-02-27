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

final class WarpcoreUtil implements WarpcoreUtilInterface
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

    public function loadWarpcore(
        ShipInterface $ship,
        int $additionalLoad,
        ?ColonyInterface $colony = null,
        ?ShipInterface $station = null
    ): ?string {
        if ($ship->getWarpcoreLoad() >= $ship->getWarpcoreCapacity()) {
            return null;
        }

        //check for core limitation
        $loadUnits = ceil($additionalLoad / ShipEnum::WARPCORE_LOAD);
        if ($loadUnits * ShipEnum::WARPCORE_LOAD > $ship->getWarpcoreCapacity() - $ship->getWarpcoreLoad()) {
            $loadUnits = ceil(($ship->getWarpcoreCapacity() - $ship->getWarpcoreLoad()) / ShipEnum::WARPCORE_LOAD);
        }

        $loadUnits = (int) $loadUnits;

        if ($loadUnits < 1) {
            return null;
        }
        $shipStorage = $ship->getStorage();

        // check for ressource limitation
        foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadUnitsCost) {
            if ($shipStorage[$commodityId]->getAmount() < ($loadUnits * $loadUnitsCost)) {
                $loadUnits = (int) ($shipStorage[$commodityId]->getAmount() / $loadUnitsCost);
            }
        }

        //consume ressources
        foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
            if ($colony !== null) {
                $this->colonyStorageManager->lowerStorage(
                    $colony,
                    $this->commodityRepository->find($commodityId),
                    $loadCost * $loadUnits
                );
            } else if ($station !== null) {
                $this->shipStorageManager->lowerStorage(
                    $station,
                    $this->commodityRepository->find($commodityId),
                    $loadCost * $loadUnits
                );
            } else {
                $this->shipStorageManager->lowerStorage(
                    $ship,
                    $shipStorage[$commodityId]->getCommodity(),
                    $loadCost * $loadUnits
                );
            }
        }

        //truncate output
        if ($ship->getWarpcoreLoad() + $loadUnits * ShipEnum::WARPCORE_LOAD > $ship->getWarpcoreCapacity()) {
            $loadUnits = $ship->getWarpcoreCapacity() - $ship->getWarpcoreLoad();
        } else {
            $loadUnits = $loadUnits * ShipEnum::WARPCORE_LOAD;
        }
        $ship->setWarpcoreLoad($ship->getWarpcoreLoad() + $loadUnits);
        $this->shipRepository->save($ship);

        if ($colony !== null) {

            $this->privateMessageSender->send(
                $colony->getUser()->getId(),
                $ship->getUser()->getId(),
                sprintf(
                    _('Die Kolonie %s hat in Sektor %s den Warpkern der %s um %d Einheiten aufgeladen'),
                    $colony->getName(),
                    $ship->getSectorString(),
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
                    _('Die %s %s hat in Sektor %s den Warpkern der %s um %d Einheiten aufgeladen'),
                    $station->getRump()->getName(),
                    $station->getName(),
                    $ship->getSectorString(),
                    $ship->getName(),
                    $loadUnits
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
                sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $ship->getId())
            );
        }

        return sprintf(
            _('%s: Der Warpkern wurde um %d Einheiten aufgeladen'),
            $ship->getName(),
            $loadUnits
        );
    }
}
