<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;
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

    public function storageContainsNeededCommodities(Collection $storages, bool $isWarpcore = true): bool
    {
        $costs = $isWarpcore ? ShipEnum::WARPCORE_LOAD_COST : ShipEnum::REACTOR_LOAD_COST;

        foreach ($costs as $commodityId => $loadCost) {
            /**
             * @var StorageInterface|null
             */
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
        bool $isWarpcore = true
    ): ?string {
        if ($ship->getReactorLoad() >= $ship->getReactorCapacity()) {
            return null;
        }

        $capaPerLoad = $isWarpcore ? ShipEnum::WARPCORE_LOAD : ShipEnum::REACTOR_LOAD;

        //check for core limitation
        $loadUnits = ceil($additionalLoad /  $capaPerLoad);
        if ($loadUnits *  $capaPerLoad > $ship->getReactorCapacity() - $ship->getReactorLoad()) {
            $loadUnits = ceil(($ship->getReactorCapacity() - $ship->getReactorLoad()) /  $capaPerLoad);
        }

        $loadUnits = (int) $loadUnits;

        if ($loadUnits < 1) {
            return null;
        }

        if ($managerProvider !== null) {
            $storage = $managerProvider->getStorage();
        } else {
            $storage = $ship->getStorage();
        }

        // check for ressource limitation
        $costs = $isWarpcore ? ShipEnum::WARPCORE_LOAD_COST : ShipEnum::REACTOR_LOAD_COST;
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
        if ($ship->getReactorLoad() + $loadUnits *  $capaPerLoad > $ship->getReactorCapacity()) {
            $loadUnits = $ship->getReactorCapacity() - $ship->getReactorLoad();
        } else {
            $loadUnits = $loadUnits *  $capaPerLoad;
        }
        $ship->setReactorLoad($ship->getReactorLoad() + $loadUnits);
        $this->shipRepository->save($ship);

        if ($managerProvider !== null) {
            $this->privateMessageSender->send(
                $managerProvider->getUser()->getId(),
                $ship->getUser()->getId(),
                sprintf(
                    _('Die %s hat in Sektor %s den %s der %s um %d Einheiten aufgeladen'),
                    $managerProvider->getName(),
                    $ship->getSectorString(),
                    $isWarpcore ? 'Warpkern' : 'Fusionsreaktor',
                    $ship->getName(),
                    $loadUnits
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
                sprintf("ship.php?%s=1&id=%d", ShowShip::VIEW_IDENTIFIER, $ship->getId())
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
