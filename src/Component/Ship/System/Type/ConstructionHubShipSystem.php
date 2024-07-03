<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;

final class ConstructionHubShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function __construct(private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository)
    {
    }

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB;
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
    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

        if (!$ship->hasEnoughCrew()) {
            $reason = _('ungenügend Crew vorhanden ist');
            return false;
        }

        return true;
    }

    #[Override]
    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $ship = $wrapper->get();
        $ship->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_ON);
        $this->shipyardShipQueueRepository->restartQueueByShipyard($ship->getId());
    }

    #[Override]
    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        $ship->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_OFF);
        $this->stopShipyardQeue($ship);
    }

    #[Override]
    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $this->stopShipyardQeue($wrapper->get());
    }

    private function stopShipyardQeue(ShipInterface $ship): void
    {
        $this->shipyardShipQueueRepository->stopQueueByShipyard($ship->getId());
    }
}
