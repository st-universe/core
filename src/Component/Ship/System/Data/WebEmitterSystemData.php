<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

class WebEmitterSystemData extends AbstractSystemData
{
    public ?int $targetId = null;
    public int $remainingTicks = 0;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function update(): void
    {
        $this->updateSystemData(
            ShipSystemTypeEnum::SYSTEM_TRACKER,
            $this,
            $this->shipSystemRepository
        );
    }

    public function getTargetWrapper(): ?ShipWrapperInterface
    {
        if ($this->targetId === null) {
            return null;
        }

        $target = $this->shipRepository->find($this->targetId);

        return $this->shipWrapperFactory->wrapShip($target);
    }

    public function setTarget(?int $targetId): WebEmitterSystemData
    {
        $this->targetId = $targetId;
        return $this;
    }

    public function isUseable(): bool
    {
        if ($this->targetId !== null) {
            return false;
        }

        $cooldown = $this->ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB)->getCooldown();

        return $cooldown === null ? true : $cooldown < time();
    }

    public function getRemainingTicks(): int
    {
        return $this->remainingTicks;
    }

    public function setRemainingTicks(int $ticks): WebEmitterSystemData
    {
        $this->remainingTicks = $ticks;
        return $this;
    }
}
