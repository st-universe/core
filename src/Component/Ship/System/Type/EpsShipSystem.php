<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

final class EpsShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public int $maxBattery = 0;
    public int $battery = 0;
    public int $batteryCooldown = 0;
    public bool $reloadBattery = false;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    public function __construct(ShipSystemRepositoryInterface $shipSystemRepository)
    {
        $this->shipSystemRepository = $shipSystemRepository;
    }

    public function update(ShipInterface $ship): void
    {
        $this->updateSystemData(
            $ship,
            ShipSystemTypeEnum::SYSTEM_EPS,
            $this,
            $this->shipSystemRepository
        );
    }

    public function getMaxBattery(): int
    {
        return $this->maxBattery;
    }

    public function setMaxBattery(int $maxBattery): EpsShipSystem
    {
        $this->maxBattery = $maxBattery;
        return $this;
    }

    public function getBattery(): int
    {
        return $this->battery;
    }

    public function setBattery(int $battery): EpsShipSystem
    {
        $this->battery = $battery;
        return $this;
    }

    public function getBatteryCooldown(): int
    {
        return $this->batteryCooldown;
    }

    public function setBatteryCooldown(int $batteryCooldown): EpsShipSystem
    {
        $this->batteryCooldown = $batteryCooldown;
        return $this;
    }

    public function reloadBattery(): bool
    {
        return $this->reloadBattery;
    }

    public function setReloadBattery(bool $reloadBattery): EpsShipSystem
    {
        $this->reloadBattery = $reloadBattery;
        return $this;
    }

    public function isEBattUseable(): bool
    {
        return $this->batteryCooldown < time();
    }

    public function activate(ShipInterface $ship, ShipSystemManagerInterface $manager): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_EPS)->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_EPS)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    public function getPriority(): int
    {
        return ShipSystemTypeEnum::SYSTEM_PRIORITIES[ShipSystemTypeEnum::SYSTEM_EPS];
    }

    public function getEnergyConsumption(): int
    {
        return 0;
    }

    public function handleDestruction(ShipInterface $ship): void
    {
        $ship->setEps(0);
    }

    public function handleDamage(ShipInterface $ship): void
    {
        if ($ship->getEps() > $ship->getMaxEps()) {
            $ship->setEps($ship->getMaxEps());
        }
    }
}
