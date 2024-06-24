<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

//TODO create unit test
final class ReactorUtil implements ReactorUtilInterface
{
    private ShipStorageManagerInterface $shipStorageManager;

    private ShipRepositoryInterface $shipRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipStorageManagerInterface $shipStorageManager,
        ShipRepositoryInterface $shipRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipStorageManager = $shipStorageManager;
        $this->shipRepository = $shipRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function storageContainsNeededCommodities(Collection $storages, ReactorWrapperInterface $reactor): bool
    {
        foreach ($reactor->get()->getLoadCost() as $commodityId => $loadCost) {
            $storage = $storages->get($commodityId);

            if ($storage === null) {
                return false;
            }
            if ($storage->getAmount() < $loadCost) {
                return false;
            }
        }

        return true;
    }

    public function loadReactor(
        ShipInterface $ship,
        int $additionalLoad,
        ?ManagerProviderInterface $managerProvider,
        ReactorWrapperInterface $reactor
    ): ?string {
        if ($reactor->getLoad() >= $reactor->getCapacity()) {
            return null;
        }

        $capaPerLoad = $reactor->get()->getLoadUnits();

        //check for core limitation
        $loadUnits = ceil($additionalLoad /  $capaPerLoad);
        if ($loadUnits *  $capaPerLoad > $reactor->getCapacity() - $reactor->getLoad()) {
            $loadUnits = ceil(($reactor->getCapacity() - $reactor->getLoad()) /  $capaPerLoad);
        }

        $loadUnits = (int) $loadUnits;

        if ($loadUnits < 1) {
            return null;
        }

        $storage = $managerProvider !== null ? $managerProvider->getStorage() : $ship->getStorage();

        // check for ressource limitation
        $costs = $reactor->get()->getLoadCost();
        foreach ($costs as $commodityId => $loadUnitsCost) {
            $storageElement = $storage->get($commodityId);

            if ($storageElement === null) {
                throw new RuntimeException('storageContainsNeededCommodities should be called first');
            }

            if ($storageElement->getAmount() < ($loadUnits * $loadUnitsCost)) {
                $loadUnits = (int) ($storageElement->getAmount() / $loadUnitsCost);
            }
        }

        //consume ressources
        foreach ($costs as $commodityId => $loadCost) {
            $storageElement = $storage->get($commodityId);

            if ($storageElement === null) {
                throw new RuntimeException('storageContainsNeededCommodities should be called first');
            }

            if ($managerProvider !== null) {
                $managerProvider->lowerStorage(
                    $storageElement->getCommodity(),
                    $loadCost * $loadUnits
                );
            } else {
                $this->shipStorageManager->lowerStorage(
                    $ship,
                    $storageElement->getCommodity(),
                    $loadCost * $loadUnits
                );
            }
        }

        //truncate output
        if ($reactor->getLoad() + $loadUnits *  $capaPerLoad > $reactor->getCapacity()) {
            $loadUnits = $reactor->getCapacity() - $reactor->getLoad();
        } else {
            $loadUnits *= $capaPerLoad;
        }
        $reactor->changeLoad($loadUnits);
        $this->shipRepository->save($ship);

        $systemName = $reactor->get()->getSystemType()->getDescription();

        if ($managerProvider !== null) {
            $this->privateMessageSender->send(
                $managerProvider->getUser()->getId(),
                $ship->getUser()->getId(),
                sprintf(
                    _('Die %s hat in Sektor %s den %s der %s um %d Einheiten aufgeladen'),
                    $managerProvider->getName(),
                    $ship->getSectorString(),
                    $systemName,
                    $ship->getName(),
                    $loadUnits
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
                sprintf("ship.php?%s=1&id=%d", ShowShip::VIEW_IDENTIFIER, $ship->getId())
            );
        }

        return sprintf(
            _('%s: Der %s wurde um %d Einheiten aufgeladen'),
            $ship->getName(),
            $systemName,
            $loadUnits
        );
    }
}
