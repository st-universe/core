<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;


class WarpCoreTransferShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public function __construct() {}

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::WARPCORE_CHARGE_TRANSFER;
    }

    #[\Override]
    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void
    {
        $wrapper->get()->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_ALWAYS_ON);
    }

    #[\Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if ($spacecraft->getCrewCount() === 0) {
            $reason = _('keine Crew vorhanden ist');
            return false;
        }

        return true;
    }

    #[\Override]
    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    #[\Override]
    public function getDefaultMode(): SpacecraftSystemModeEnum
    {
        return SpacecraftSystemModeEnum::MODE_ALWAYS_ON;
    }
}
