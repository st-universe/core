<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

class WarpDriveSystemData extends AbstractSystemData
{
    // warpdrive fields
    public int $wd = 0;
    public int $maxwd = 0;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    public function __construct(ShipSystemRepositoryInterface $shipSystemRepository)
    {
        $this->shipSystemRepository = $shipSystemRepository;
    }

    public function update(): void
    {
        $this->updateSystemData(
            ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
            $this,
            $this->shipSystemRepository
        );
    }

    public function getWarpDrive(): int
    {
        return $this->wd;
    }

    public function setWarpDrive(int $wd): WarpDriveSystemData
    {
        $this->wd = $wd;
        return $this;
    }

    public function lowerWarpDrive(int $amount): WarpDriveSystemData
    {
        $this->wd -= $amount;
        return $this;
    }

    public function setMaxWarpDrive(int $maxwd): WarpDriveSystemData
    {
        $this->maxwd = $maxwd;
        return $this;
    }

    public function getTheoreticalMaxWarpdrive(): int
    {
        return $this->maxwd;
    }

    /**
     * proportional to warpdrive system status
     */
    public function getMaxWarpDrive(): int
    {
        if (!$this->ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)) {
            return $this->maxwd;
        }

        return (int) (ceil($this->maxwd
            * $this->ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)->getStatus() / 100));
    }

    public function getWarpDriveStatusBar(): string
    {
        return $this->getTalStatusBar(
            _('Warpantrieb'),
            $this->getWarpDrive(),
            $this->getMaxWarpDrive(),
            StatusBarColorEnum::STATUSBAR_BLUE
        )
            ->render();
    }

    public function getWarpDriveStatusBarBig(): string
    {
        return $this->getTalStatusBar(
            _('Warpantrieb'),
            $this->getWarpDrive(),
            $this->getMaxWarpDrive(),
            StatusBarColorEnum::STATUSBAR_BLUE
        )
            ->setSizeModifier(1.6)
            ->render();
    }
}
