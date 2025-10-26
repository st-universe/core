<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShuttleRampShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public function __construct(private StorageManagerInterface $storageManager) {}

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::SHUTTLE_RAMP;
    }

    #[\Override]
    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void
    {
        //nothing to do here
    }

    #[\Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        //nothing to do here
    }

    #[\Override]
    public function getDefaultMode(): SpacecraftSystemModeEnum
    {
        return SpacecraftSystemModeEnum::MODE_ALWAYS_OFF;
    }

    #[\Override]
    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    #[\Override]
    public function getEnergyConsumption(): int
    {
        return 0;
    }

    #[\Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        // delete shuttles
        $spacecraft = $wrapper->get();
        foreach ($spacecraft->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                $this->storageManager->lowerStorage($spacecraft, $stor->getCommodity(), $stor->getAmount());
            }
        }
    }
}
