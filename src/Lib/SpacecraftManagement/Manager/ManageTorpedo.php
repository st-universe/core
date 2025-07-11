<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Override;
use RuntimeException;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TorpedoType;

class ManageTorpedo implements ManagerInterface
{
    public function __construct(
        private readonly ShipTorpedoManagerInterface $shipTorpedoManager,
        private readonly PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function manage(SpacecraftWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $torp = $values['torp'] ?? null;
        if ($torp === null) {
            throw new RuntimeException('value array not existent');
        }

        $ship = $wrapper->get();

        if (!array_key_exists($ship->getId(), $torp)) {
            return [];
        }

        $count = $this->determineCount($torp[$ship->getId()], $ship);

        if ($count < 0) {
            return [];
        }
        if ($count === $ship->getTorpedoCount()) {
            return [];
        }

        $load = $count - $ship->getTorpedoCount();
        $isUnload = $load < 0;

        if ($isUnload) {
            return $this->unloadTorpedo(abs($load), $wrapper, $managerProvider);
        } else {
            $selectedTorpedoTypeArray = $values['torp_type'] ?? null;
            $torpedoType = $this->determineTorpedoType($wrapper, $selectedTorpedoTypeArray);

            return $this->loadTorpedo($load, $torpedoType, $wrapper, $managerProvider);
        }
    }

    private function determineCount(mixed $value, Spacecraft $spacecraft): int
    {
        if ($value == 'm') {
            return $spacecraft->getMaxTorpedos();
        } else {
            $count = (int) $value;

            if ($count > $spacecraft->getMaxTorpedos()) {
                $count = $spacecraft->getMaxTorpedos();
            }

            return $count;
        }
    }

    /**
     * @return array<string>
     */
    private function unloadTorpedo(int $unload, SpacecraftWrapperInterface $wrapper, ManagerProviderInterface $managerProvider): array
    {
        $user = $managerProvider->getUser();
        $ship = $wrapper->get();

        if ($ship->getUser() !== $user) {
            return [];
        }

        $torpedoType = $ship->getTorpedo();

        if ($torpedoType === null) {
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
    private function determineTorpedoType(SpacecraftWrapperInterface $wrapper, ?array $selectedTorpedoTypeArray): ?TorpedoType
    {
        $spacecraft = $wrapper->get();

        if ($spacecraft->getTorpedoCount() > 0) {
            return $spacecraft->getTorpedo();
        }

        if ($selectedTorpedoTypeArray === null) {
            return null;
        }

        if (!array_key_exists($spacecraft->getId(), $selectedTorpedoTypeArray)) {
            return null;
        }

        $selectedTorpedoTypeId = (int) $selectedTorpedoTypeArray[$spacecraft->getId()];
        $possibleTorpedoTypes = $wrapper->getPossibleTorpedoTypes();

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
        ?TorpedoType $torpedoType,
        SpacecraftWrapperInterface $wrapper,
        ManagerProviderInterface $managerProvider
    ): array {
        $ship = $wrapper->get();

        if ($torpedoType === null) {
            return [];
        }

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
        TorpedoType $torpedoType,
        ManagerProviderInterface $managerProvider,
        Spacecraft $spacecraft
    ): void {
        $this->privateMessageSender->send(
            $managerProvider->getUser()->getId(),
            $spacecraft->getUser()->getId(),
            sprintf(
                _('Die %s hat in Sektor %s %d %s auf die %s transferiert'),
                $managerProvider->getName(),
                $spacecraft->getSectorString(),
                $load,
                $torpedoType->getName(),
                $spacecraft->getName()
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
            $spacecraft
        );
    }
}
