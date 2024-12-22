<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

class ManageTorpedo implements ManagerInterface
{
    public function __construct(
        private TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        private ShipTorpedoManagerInterface $shipTorpedoManager,
        private PrivateMessageSenderInterface $privateMessageSender
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
            $torpedoType = $this->determineTorpedoType($ship, $selectedTorpedoTypeArray);

            return $this->loadTorpedo($load, $torpedoType, $wrapper, $managerProvider);
        }
    }

    private function determineCount(mixed $value, SpacecraftInterface $spacecraft): int
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
     * @return array<int, TorpedoTypeInterface>
     */
    private function getPossibleTorpedoTypes(SpacecraftInterface $spacecraft): array
    {
        if ($spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)) {
            return $this->torpedoTypeRepository->getAll();
        } else {
            return $this->torpedoTypeRepository->getByLevel($spacecraft->getRump()->getTorpedoLevel());
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
    private function determineTorpedoType(SpacecraftInterface $spacecraft, ?array $selectedTorpedoTypeArray): ?TorpedoTypeInterface
    {
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
        $possibleTorpedoTypes = $this->getPossibleTorpedoTypes($spacecraft);

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
        TorpedoTypeInterface $torpedoType,
        ManagerProviderInterface $managerProvider,
        SpacecraftInterface $spacecraft
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
            $spacecraft->getHref()
        );
    }
}
