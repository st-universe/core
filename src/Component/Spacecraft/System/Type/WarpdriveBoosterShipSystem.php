<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Game\TimeConstants;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class WarpdriveBoosterShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::WARPDRIVE_BOOSTER;
    }

    #[\Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if (!$spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::WARPDRIVE)) {
            $reason = _('der Warpantrieb beschädigt ist');
            return false;
        }

        return true;
    }

    #[\Override]
    public function getCooldownSeconds(): int
    {
        return TimeConstants::ONE_DAY_IN_SECONDS;
    }

    #[\Override]
    public function getEnergyUsageForActivation(): int
    {
        return 100;
    }
}
