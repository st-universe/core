<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;

final class ConstructionHubShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public function __construct(private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository) {}

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 20;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 10;
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if (!$spacecraft->hasEnoughCrew()) {
            $reason = _('ungenügend Crew vorhanden ist');
            return false;
        }

        return true;
    }

    #[Override]
    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void
    {
        $spacecraft = $wrapper->get();
        $spacecraft->getShipSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_ON);
        $this->shipyardShipQueueRepository->restartQueueByShipyard($spacecraft->getId());
    }

    #[Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        $spacecraft = $wrapper->get();
        $spacecraft->getShipSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        $this->stopShipyardQeue($spacecraft);
    }

    #[Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        $this->stopShipyardQeue($wrapper->get());
    }

    private function stopShipyardQeue(SpacecraftInterface $spacecraft): void
    {
        $this->shipyardShipQueueRepository->stopQueueByShipyard($spacecraft->getId());
    }
}
