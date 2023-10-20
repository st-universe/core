<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class ShuttleRampShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private ShipStorageManagerInterface $shipStorageManager;

    public function __construct(ShipStorageManagerInterface $shipStorageManager)
    {
        $this->shipStorageManager = $shipStorageManager;
    }

    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP;
    }

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        //nothing to do here
    }

    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        //nothing to do here
    }

    public function getDefaultMode(): int
    {
        return ShipSystemModeEnum::MODE_ALWAYS_OFF;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    public function getEnergyConsumption(): int
    {
        return 0;
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        // delete shuttles
        $ship = $wrapper->get();
        foreach ($ship->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                $this->shipStorageManager->lowerStorage($ship, $stor->getCommodity(), $stor->getAmount());
            }
        }
    }
}
