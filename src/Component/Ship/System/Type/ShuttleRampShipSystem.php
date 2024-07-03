<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class ShuttleRampShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function __construct(private ShipStorageManagerInterface $shipStorageManager)
    {
    }

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP;
    }

    #[Override]
    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        //nothing to do here
    }

    #[Override]
    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        //nothing to do here
    }

    #[Override]
    public function getDefaultMode(): int
    {
        return ShipSystemModeEnum::MODE_ALWAYS_OFF;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 0;
    }

    #[Override]
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
