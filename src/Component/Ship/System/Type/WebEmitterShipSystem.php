<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Interaction\TholianWebUtilInterface;

class WebEmitterShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function __construct(private TholianWebUtilInterface $tholianWebUtil)
    {
    }

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB;
    }

    #[Override]
    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $this->checkForWebAbortion($wrapper);
        $wrapper->get()->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    #[Override]
    public function getCooldownSeconds(): ?int
    {
        return TimeConstants::ONE_DAY_IN_SECONDS;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 30;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 10;
    }

    #[Override]
    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $this->checkForWebAbortion($wrapper);
    }

    private function checkForWebAbortion(ShipWrapperInterface $wrapper): void
    {
        $webUnderConstruction = $wrapper->getWebEmitterSystemData()->getWebUnderConstruction();

        if ($webUnderConstruction === null) {
            return;
        }

        $this->tholianWebUtil->releaseWebHelper($wrapper);
    }
}
