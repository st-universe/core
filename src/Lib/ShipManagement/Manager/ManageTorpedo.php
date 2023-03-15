<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use RuntimeException;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

class ManageTorpedo implements ManagerInterface
{
    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private ShipTorpedoManagerInterface $shipTorpedoManager;

    private PrivateMessageSenderInterface $privateMessageSender;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        ShipTorpedoManagerInterface $shipTorpedoManager,
        PrivateMessageSenderInterface $privateMessageSender,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->shipTorpedoManager = $shipTorpedoManager;
        $this->privateMessageSender = $privateMessageSender;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function manage(ShipWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $this->loggerUtil->init('torp', LoggerEnum::LEVEL_WARNING);

        $torp = $values['torp'] ?? null;
        if ($torp === null) {
            throw new RuntimeException('value array not existent');
        }

        $this->loggerUtil->log('A');
        $ship = $wrapper->get();

        if (!array_key_exists($ship->getId(), $torp)) {
            $this->loggerUtil->log('B');
            return [];
        }

        $count = $this->determineCount($torp[$ship->getId()], $ship);

        if ($count < 0) {
            $this->loggerUtil->log('C');
            return [];
        }
        if ($count === $ship->getTorpedoCount()) {
            $this->loggerUtil->log('D');
            return [];
        }

        $load = $count - $ship->getTorpedoCount();
        $isUnload = $load < 0;

        if ($isUnload) {
            $this->loggerUtil->log('E');

            return $this->unloadTorpedo((int)abs($load), $wrapper, $managerProvider);
        } else {
            $this->loggerUtil->log('F');
            $selectedTorpedoTypeArray = $values['torp_type'] ?? null;
            $torpedoType = $this->determineTorpedoType($ship, $selectedTorpedoTypeArray);

            return $this->loadTorpedo($load, $torpedoType, $wrapper, $managerProvider);
        }
    }

    private function determineCount(mixed $value, ShipInterface $ship): int
    {
        if ($value == 'm') {
            return $ship->getMaxTorpedos();
        } else {
            $count = (int) $value;

            if ($count > $ship->getMaxTorpedos()) {
                $count = $ship->getMaxTorpedos();
            }

            return $count;
        }
    }

    /**
     * @return array<int, TorpedoTypeInterface>
     */
    private function getPossibleTorpedoTypes(ShipInterface $ship): array
    {
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            return $this->torpedoTypeRepository->getAll();
        } else {
            return $this->torpedoTypeRepository->getByLevel($ship->getRump()->getTorpedoLevel());
        }
    }

    /**
     * @return array<string>
     */
    private function unloadTorpedo(int $unload, ShipWrapperInterface $wrapper, ManagerProviderInterface $managerProvider): array
    {
        $user = $managerProvider->getUser();
        $ship = $wrapper->get();

        if ($ship->getUser() !== $user) {
            $this->loggerUtil->log('G');
            return [];
        }

        $torpedoType = $ship->getTorpedo();

        if ($torpedoType === null) {
            $this->loggerUtil->log('H');
            return [];
        }

        $managerProvider->upperStorage($torpedoType->getCommodity(), $unload);
        $this->shipTorpedoManager->changeTorpedo($wrapper, -$unload);

        return [sprintf(
            _('%s: Es wurden %d Torpedos des Typs %s vom Schiff transferiert'),
            $ship->getName(),
            $unload,
            $torpedoType->getName()
        )];
    }

    /**
     * @param array<int|string, mixed>|null $selectedTorpedoTypeArray
     */
    private function determineTorpedoType(ShipInterface $ship, ?array $selectedTorpedoTypeArray): ?TorpedoTypeInterface
    {
        if ($ship->getTorpedoCount() > 0) {
            return $ship->getTorpedo();
        }

        if ($selectedTorpedoTypeArray === null) {
            return null;
        }

        if (!array_key_exists($ship->getId(), $selectedTorpedoTypeArray)) {
            return null;
        }

        $selectedTorpedoTypeId = (int) $selectedTorpedoTypeArray[$ship->getId()];
        $possibleTorpedoTypes = $this->getPossibleTorpedoTypes($ship);

        if (!array_key_exists($selectedTorpedoTypeId, $possibleTorpedoTypes)) {
            return null;
        }

        return $possibleTorpedoTypes[$selectedTorpedoTypeId];
    }

    /**
     * @return array<string>
     */
    private function loadTorpedo(
        int $load,
        ?TorpedoTypeInterface $torpedoType,
        ShipWrapperInterface $wrapper,
        ManagerProviderInterface $managerProvider
    ): array {
        $ship = $wrapper->get();

        if ($torpedoType === null) {
            $this->loggerUtil->log('I');
            return [];
        }

        /**
         * @var StorageInterface|null
         */
        $storageElement = $managerProvider->getStorage()->get($torpedoType->getCommodityId());
        if ($storageElement === null) {
            return [sprintf(
                _('%s: Es sind keine Torpedos des Typs %s auf der %s vorhanden'),
                $ship->getName(),
                $torpedoType->getName(),
                $managerProvider->getName()
            )];
        }

        if ($load > $storageElement->getAmount()) {
            $load = $storageElement->getAmount();
        }

        $managerProvider->lowerStorage(
            $torpedoType->getCommodity(),
            $load
        );

        if ($ship->getTorpedoCount() === 0) {
            $this->shipTorpedoManager->changeTorpedo($wrapper, $load, $torpedoType);
        } else {
            $this->shipTorpedoManager->changeTorpedo($wrapper, $load);
        }

        $this->sendMessage(
            $load,
            $torpedoType,
            $managerProvider,
            $ship
        );

        return  [sprintf(
            _('%s: Es wurden %d Torpedos des Typs %s zum Schiff transferiert'),
            $ship->getName(),
            $load,
            $torpedoType->getName()
        )];
    }

    private function sendMessage(
        int $load,
        TorpedoTypeInterface $torpedoType,
        ManagerProviderInterface $managerProvider,
        ShipInterface $ship
    ): void {
        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $ship->getId());

        $this->privateMessageSender->send(
            $managerProvider->getUser()->getId(),
            $ship->getUser()->getId(),
            sprintf(
                _('Die %s hat in Sektor %s %d %s auf die %s transferiert'),
                $managerProvider->getName(),
                $ship->getSectorString(),
                $load,
                $torpedoType->getName(),
                $ship->getName()
            ),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            $href
        );
    }
}
