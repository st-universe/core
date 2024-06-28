<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use RuntimeException;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

class TrackerSystemData extends AbstractSystemData
{
    public ?int $targetId = null;
    public int $remainingTicks = 0;

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        ShipSystemRepositoryInterface $shipSystemRepository
    ) {
        parent::__construct($shipSystemRepository);
    }

    function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_TRACKER;
    }

    public function getTargetWrapper(): ?ShipWrapperInterface
    {
        if ($this->targetId === null) {
            return null;
        }

        $target = $this->shipRepository->find($this->targetId);

        if ($target === null) {
            throw new RuntimeException('target ship is not existent');
        }

        return $this->shipWrapperFactory->wrapShip($target);
    }

    public function setTarget(?int $targetId): TrackerSystemData
    {
        $this->targetId = $targetId;
        return $this;
    }

    public function isUseable(): bool
    {
        if ($this->targetId !== null) {
            return false;
        }

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

    public function canAttackCloakedTarget(): bool
    {
        $targetWrapper = $this->getTargetWrapper();
        if ($targetWrapper === null) {
            return false;
        }

        $target = $targetWrapper->get();

        return (new InteractionChecker())->checkPosition($this->ship, $target) && $target->getCloakState();
    }
}
