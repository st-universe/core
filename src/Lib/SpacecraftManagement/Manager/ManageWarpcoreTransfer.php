<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderSpacecraft;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class ManageWarpcoreTransfer implements ManagerInterface
{
    #[\Override]
    public function manage(SpacecraftWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $values = $values['warpcore_transfer'] ?? null;
        if ($values === null) {
            throw new RuntimeException('value array not existent');
        }

        $targetSpacecraft = $wrapper->get();

        if (!array_key_exists($targetSpacecraft->getId(), $values)) {
            return [];
        }

        if ($values[$targetSpacecraft->getId()] === '' || $values[$targetSpacecraft->getId()] < 1) {
            return [];
        }

        if (!$managerProvider instanceof ManagerProviderSpacecraft) {
            return [];
        }

        $requestedAmount = (int)$values[$targetSpacecraft->getId()];

        $targetReactor = $wrapper->getReactorWrapper();
        if ($targetReactor === null) {
            return [];
        }

        if (
            $targetSpacecraft->getSystemState(SpacecraftSystemTypeEnum::WARPDRIVE) ||
            $targetSpacecraft->getSystemState(SpacecraftSystemTypeEnum::SHIELDS)
        ) {
            return [sprintf(
                '%s: Warpantrieb und Schilde müssen deaktiviert sein',
                $targetSpacecraft->getName()
            )];
        }

        $availableSourceLoad = $managerProvider->getReactorLoad();
        $targetCapacity = $targetReactor->getCapacity() - $targetReactor->getLoad();

        if ($availableSourceLoad <= 0) {
            return [];
        }

        if ($targetCapacity <= 0) {
            return [sprintf(
                '%s: Warpkern ist bereits voll geladen',
                $targetSpacecraft->getName()
            )];
        }

        $actualTransfer = min($requestedAmount, $availableSourceLoad, $targetCapacity);

        $targetReactor->changeLoad((int)$actualTransfer);
        $managerProvider->lowerReactorLoad((int)$actualTransfer);

        return [sprintf(
            '%s: Der Warpkern wurde um %d Einheiten aufgeladen',
            $targetSpacecraft->getName(),
            $actualTransfer
        )];
    }
}
