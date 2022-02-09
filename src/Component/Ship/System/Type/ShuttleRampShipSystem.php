<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\Storage\ShipStorageManager;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShuttleRampShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private ShipStorageManagerInterface $shipStorageManager;

    public function __construct(ShipStorageManagerInterface $shipStorageManager)
    {
        $this->shipStorageManager = $shipStorageManager;
    }
    public function activate(ShipInterface $ship): void
    {
        //nothing to do here
    }

    public function deactivate(ShipInterface $ship): void
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

    public function handleDestruction(ShipInterface $ship): void
    {
        // delete shuttles
        foreach ($ship->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                $this->shipStorageManager->lowerStorage($ship, $stor->getCommodity(), $stor->getAmount());
            }
        }
    }
}
