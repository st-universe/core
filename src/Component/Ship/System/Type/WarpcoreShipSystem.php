<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use RuntimeException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class WarpcoreShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_WARPCORE;
    }

    #[Override]
    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $reactor = $wrapper->getReactorWrapper();
        if ($reactor === null) {
            throw new RuntimeException('this should not happen');
        }

        if ($reactor->getLoad() === 0) {
            $reason = _('keine Warpkernladung vorhanden ist');
            return false;
        }

        return true;
    }

    #[Override]
    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $wrapper->get()->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 0;
    }

    #[Override]
    public function getDefaultMode(): int
    {
        return ShipSystemModeEnum::MODE_ALWAYS_ON;
    }

    #[Override]
    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_WARPDRIVE)->setMode(ShipSystemModeEnum::MODE_OFF);
        }
    }
}
