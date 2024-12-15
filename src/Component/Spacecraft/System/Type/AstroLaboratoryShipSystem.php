<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class AstroLaboratoryShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public const int FINALIZING_ENERGY_COST = 15;

    public function __construct(private AstroEntryLibInterface $astroEntryLib) {}

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY;
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if ($spacecraft->getCloakState()) {
            $reason = _('die Tarnung aktiv ist');
            return false;
        }

        if (!$spacecraft->getLss()) {
            $reason = _('die Langstreckensensoren nicht aktiv sind');
            return false;
        }

        if (!$spacecraft->getNbs()) {
            $reason = _('die Nahbereichssensoren nicht aktiv sind');
            return false;
        }

        return true;
    }

    #[Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        $spacecraft = $wrapper->get();
        if ($spacecraft->getState() === SpacecraftStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $this->astroEntryLib->cancelAstroFinalizing($wrapper);
        }

        $spacecraft->getShipSystem(SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
    }

    #[Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        $spacecraft = $wrapper->get();
        if (
            $spacecraft->getState() === SpacecraftStateEnum::SHIP_STATE_ASTRO_FINALIZING
            && $wrapper instanceof ShipWrapperInterface
        ) {
            $this->astroEntryLib->cancelAstroFinalizing($wrapper);
        }
    }
}
