<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class CloakShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public function __construct(private SpacecraftStateChangerInterface $spacecraftStateChanger) {}

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::CLOAK;
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if ($spacecraft->isTractoring()) {
            $reason = _('das Schiff den Traktorstrahl aktiviert hat');
            return false;
        }

        if ($spacecraft instanceof ShipInterface && $spacecraft->isTractored()) {
            $reason = _('das Schiff von einem Traktorstrahl gehalten wird');
            return false;
        }

        if ($spacecraft->getSubspaceState()) {
            $reason = _('die Subraumfeldsensoren aktiv sind');
            return false;
        }

        if ($spacecraft->getAlertState() == SpacecraftAlertStateEnum::ALERT_RED) {
            $reason = _('die Alarmstufe Rot ist');
            return false;
        }

        return true;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 10;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 8;
    }

    #[Override]
    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void
    {
        $spacecraft = $wrapper->get();
        if ($spacecraft->isTractoring()) {
            $manager->deactivate($wrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true);
        }

        if ($spacecraft instanceof ShipInterface) {
            $spacecraft->setDockedTo(null);
        }
        $this->spacecraftStateChanger->changeShipState($wrapper, SpacecraftStateEnum::SHIP_STATE_NONE);

        if ($spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::ASTRO_LABORATORY)) {
            $spacecraft->getShipSystem(SpacecraftSystemTypeEnum::ASTRO_LABORATORY)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }
        if ($spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::SHIELDS)) {
            $spacecraft->getShipSystem(SpacecraftSystemTypeEnum::SHIELDS)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }
        if ($spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::PHASER)) {
            $spacecraft->getShipSystem(SpacecraftSystemTypeEnum::PHASER)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }
        if ($spacecraft->hasShipSystem(SpacecraftSystemTypeEnum::TORPEDO)) {
            $spacecraft->getShipSystem(SpacecraftSystemTypeEnum::TORPEDO)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }

        $spacecraft->getShipSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_ON);
    }
}
