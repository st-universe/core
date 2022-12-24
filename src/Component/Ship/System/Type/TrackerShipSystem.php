<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Game\TimeConstants;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

class TrackerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public ?int $targetId = null;
    public int $start = 0;

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

    public function setTarget(?int $targetId): TrackerShipSystem
    {
        $this->targetId = $targetId;
        return $this;
    }

    public function getCooldownSeconds(): ?int
    {
        return TimeConstants::ONE_HOUR_IN_SECONDS;
    }

    public function isUseable(): bool
    {
        $cooldown = $this->ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACKER)->getCooldown();

        return $cooldown === null ? true : $cooldown < time();
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function setStart(int $start): TrackerShipSystem
    {
        $this->start = $start;
        return $this;
    }

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $wrapper->get()->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACKER)->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACKER)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function getEnergyUsageForActivation(): int
    {
        return 15;
    }

    public function getEnergyConsumption(): int
    {
        return 7;
    }

    public function handleDestruction(ShipInterface $ship): void
    {
        $this->setTarget(null)->update();
    }
}
