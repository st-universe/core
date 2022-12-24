<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Tal\TalStatusBar;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

abstract class AbstractShipSystemType implements ShipSystemTypeInterface
{
    protected ShipInterface $ship;

    public function setShip(ShipInterface $ship): void
    {
        $this->ship = $ship;
    }
    /**
     * updates the system metadata for this specific ship system
     */
    protected function updateSystemData(
        int $systemType,
        $data,
        ShipSystemRepositoryInterface $shipSystemRepository
    ): void {
        $system = $this->ship->getShipSystem($systemType);
        $system->setData(json_encode($data));
        $shipSystemRepository->save($system);
    }

    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        return true;
    }

    public function checkDeactivationConditions(ShipInterface $ship, &$reason): bool
    {
        return true;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    public function getPriority(): int
    {
        return ShipSystemTypeEnum::SYSTEM_PRIORITY_STANDARD;
    }

    public function getEnergyConsumption(): int
    {
        return 1;
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        //nothing to do here
    }

    public function handleDamage(ShipWrapperInterface $wrapper): void
    {
        //nothing to do here
    }

    public function getDefaultMode(): int
    {
        return ShipSystemModeEnum::MODE_OFF;
    }

    public function getCooldownSeconds(): ?int
    {
        return null;
    }

    protected function getTalStatusBar(string $label, int $value, int $maxValue, $color): TalStatusBar
    {
        return (new TalStatusBar())
            ->setColor($color)
            ->setLabel($label)
            ->setMaxValue($maxValue)
            ->setValue($value);
    }
}
