<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TorpedoType;

class ManageTorpedoReserves implements ManagerInterface
{
    public function __construct(private readonly ShipTorpedoManagerInterface $shipTorpedoManager) {}

    #[\Override]
    public function manage(SpacecraftWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $reserveValues = $values['torp_reserve'] ?? null;
        $readyValues = $values['torp_ready'] ?? null;

        $spacecraft = $wrapper->get();
        $messages = [];
        $possibleTorpedoTypes = $wrapper->getPossibleTorpedoTypes();
        if ($spacecraft->isTorpedoStorageHealthy()) {
            $shipReserveValues = is_array($reserveValues) ? $reserveValues[$spacecraft->getId()] ?? [] : [];
            foreach ($shipReserveValues as $torpedoTypeId => $value) {
                $torpedoType = $possibleTorpedoTypes[(int) $torpedoTypeId] ?? null;
                if ($torpedoType === null || $this->isFireableByRump($spacecraft, $torpedoType)) {
                    continue;
                }

                $messages = array_merge(
                    $messages,
                    $this->changeReserveAmount($wrapper, $managerProvider, $torpedoType, $value)
                );
            }
        }

        $shipReadyValues = is_array($readyValues) ? $readyValues[$spacecraft->getId()] ?? [] : [];
        foreach ($shipReadyValues as $torpedoTypeId => $value) {
            $torpedoType = $possibleTorpedoTypes[(int) $torpedoTypeId] ?? null;
            if ($torpedoType === null || !$this->isFireableByRump($spacecraft, $torpedoType)) {
                continue;
            }

            $messages = array_merge(
                $messages,
                $this->changeReserveAmount($wrapper, $managerProvider, $torpedoType, $value)
            );
        }

        return $messages;
    }

    private function isFireableByRump(Spacecraft $spacecraft, TorpedoType $torpedoType): bool
    {
        return $spacecraft->getRump()->getTorpedoLevel() === $torpedoType->getLevel();
    }

    /** @return array<string> */
    private function changeReserveAmount(
        SpacecraftWrapperInterface $wrapper,
        ManagerProviderInterface $managerProvider,
        TorpedoType $torpedoType,
        mixed $value
    ): array {
        if (!is_numeric($value)) {
            return [];
        }

        $spacecraft = $wrapper->get();
        $currentAmount = $spacecraft->getTorpedoStorageForType($torpedoType)?->getStorage()->getAmount() ?? 0;
        $targetAmount = max(0, (int) $value);
        $changeAmount = $targetAmount - $currentAmount;

        if ($changeAmount === 0) {
            return [];
        }

        if ($changeAmount < 0) {
            if ($spacecraft->getUser()->getId() !== $managerProvider->getUser()->getId()) {
                return [];
            }

            $amount = abs($changeAmount);
            $managerProvider->upperStorage($torpedoType->getCommodity(), $amount);
            $this->shipTorpedoManager->changeTorpedo($wrapper, -$amount, $torpedoType);

            return [sprintf(
                _('%s: Es wurden %d Torpedos des Typs %s aus dem Torpedolager transferiert'),
                $spacecraft->getName(),
                $amount,
                $torpedoType->getName()
            )];
        }

        $sourceStorage = $managerProvider->getStorage()->get($torpedoType->getCommodityId());
        if ($sourceStorage === null) {
            return [];
        }

        $freeCapacity = $spacecraft->getMaxTorpedos() - $spacecraft->getTotalTorpedoCount();
        $amount = min($changeAmount, $sourceStorage->getAmount(), $freeCapacity);
        if ($amount < 1) {
            return [];
        }

        $managerProvider->lowerStorage($torpedoType->getCommodity(), $amount);
        $this->shipTorpedoManager->changeTorpedo($wrapper, $amount, $torpedoType);

        return [sprintf(
            _('%s: Es wurden %d Torpedos des Typs %s in das Torpedolager geladen'),
            $spacecraft->getName(),
            $amount,
            $torpedoType->getName()
        )];
    }
}
