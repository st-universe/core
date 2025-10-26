<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;

class TrackerSystemData extends AbstractSystemData
{
    public ?int $targetId = null;
    public int $remainingTicks = 0;

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        SpacecraftSystemRepositoryInterface $shipSystemRepository,
        StatusBarFactoryInterface $statusBarFactory
    ) {
        parent::__construct($shipSystemRepository, $statusBarFactory);
    }

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::TRACKER;
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

        return $this->spacecraftWrapperFactory->wrapShip($target);
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

        $cooldown = $this->spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACKER)->getCooldown();

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

        return $this->spacecraft->getLocation() ===  $target->getLocation()
            && $target->isCloaked();
    }
}
