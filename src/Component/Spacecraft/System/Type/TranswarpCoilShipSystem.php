<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;

final class TranswarpCoilShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::TRANSWARP_COIL;
    }

    #[\Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if ($spacecraft instanceof Ship && $spacecraft->isTractored()) {
            $reason = _('es von einem Traktorstrahl gehalten wird');
            return false;
        }

        return true;
    }

    #[\Override]
    public function getCooldownSeconds(): int
    {
        return 300;
    }

    #[\Override]
    public function getEnergyUsageForActivation(): int
    {
        return 55;
    }
}
