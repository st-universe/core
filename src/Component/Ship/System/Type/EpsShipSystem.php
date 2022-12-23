<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

class EpsShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    // eps fields
    public int $eps = 0;
    public int $maxEps = 0;

    // battery fields
    public int $maxBattery = 0;
    public int $battery = 0;
    public int $batteryCooldown = 0;
    public bool $reloadBattery = false;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    public function __construct(ShipSystemRepositoryInterface $shipSystemRepository)
    {
        $this->shipSystemRepository = $shipSystemRepository;
    }

    public function update(): void
    {
        $this->updateSystemData(
            ShipSystemTypeEnum::SYSTEM_EPS,
            $this,
            $this->shipSystemRepository
        );
    }

    public function getEps(): int
    {
        return $this->eps;
    }

    public function setEps(int $eps): EpsShipSystem
    {
        $this->eps = $eps;
        return $this;
    }

    public function setMaxEps(int $maxEps): EpsShipSystem
    {
        $this->maxEps = $maxEps;
        $this->maxBattery = (int) round($maxEps / 3);
        return $this;
    }

    public function getTheoreticalMaxEps(): int
    {
        return $this->maxEps;
    }

    /**
     * proportional to eps system status
     */
    public function getMaxEps(): int
    {
        if (!$this->ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_EPS)) {
            return $this->maxEps;
        }

        return (int) (ceil($this->maxEps
            * $this->ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_EPS)->getStatus() / 100));
    }

    public function getMaxBattery(): int
    {
        return $this->maxBattery;
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

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $$wrapper->get()->getShipSystem(ShipSystemTypeEnum::SYSTEM_EPS)->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);
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
        $this->setEps(0)->update();
    }

    public function handleDamage(ShipInterface $ship): void
    {
        if ($this->getEps() > $this->getMaxEps()) {
            $this->setEps($this->getMaxEps())->update();
        }
    }

    public function getEpsStatusBar()
    {
        return $this->getTalStatusBar(
            _('Energie'),
            $this->getEps(),
            $this->getMaxEps(),
            StatusBarColorEnum::STATUSBAR_YELLOW
        )
            ->render();
    }

    public function getEpsStatusBarBig()
    {
        return $this->getTalStatusBar(
            _('Energie'),
            $this->getEps(),
            $this->getMaxEps(),
            StatusBarColorEnum::STATUSBAR_YELLOW
        )
            ->setSizeModifier(1.6)
            ->render();
    }
}
