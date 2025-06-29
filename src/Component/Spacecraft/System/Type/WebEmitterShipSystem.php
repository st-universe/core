<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class WebEmitterShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public function __construct(private TholianWebUtilInterface $tholianWebUtil) {}

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::THOLIAN_WEB;
    }

    #[Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        $this->checkForWebAbortion($wrapper);
        $wrapper->get()->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_OFF);
    }

    #[Override]
    public function getCooldownSeconds(): int
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
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        $this->checkForWebAbortion($wrapper);
    }

    private function checkForWebAbortion(SpacecraftWrapperInterface $wrapper): void
    {
        if (!$wrapper instanceof ShipWrapperInterface) {
            return;
        }

        $webUnderConstruction = $wrapper->getWebEmitterSystemData()?->getWebUnderConstruction();
        if ($webUnderConstruction === null) {
            return;
        }

        $this->tholianWebUtil->releaseWebHelper($wrapper);
    }
}
