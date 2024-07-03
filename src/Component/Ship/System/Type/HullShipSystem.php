<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class HullShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_HULL;
    }

    #[Override]
    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        //nothing to do here
    }

    #[Override]
    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        //nothing to do here
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
}
