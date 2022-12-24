<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

class TrackerSystemData extends AbstractSystemData
{
    public ?int $targetId = null;
    public int $remainingTicks = 0;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipSystemRepositoryInterface $shipSystemRepository
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipSystemRepository = $shipSystemRepository;
    }

    public function update(): void
    {
        $this->updateSystemData(
            ShipSystemTypeEnum::SYSTEM_TRACKER,
            $this,
            $this->shipSystemRepository
        );
    }

    public function getTarget(): ?ShipInterface
    {
        return $this->targetId === null ? null : $this->shipRepository->find($this->targetId);
    }

    public function setTarget(?int $targetId): TrackerSystemData
    {
        $this->targetId = $targetId;
        return $this;
    }

    public function isUseable(): bool
    {
        $cooldown = $this->ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACKER)->getCooldown();

        return $cooldown === null ? true : $cooldown < time();
    }

    public function getRemainingTicks(): int
    {
        return $this->remainingTicks;
    }

    public function setRemainingTicks(int $ticks): TrackerSystemData
    {
        $this->remainingTicks = $ticks;
        return $this;
    }
}
