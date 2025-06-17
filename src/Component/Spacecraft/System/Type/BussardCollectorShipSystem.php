<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class BussardCollectorShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public function __construct(private SpacecraftStateChangerInterface $spacecraftStateChanger) {}

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR;
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if ($spacecraft->isTractoring()) {
            $reason = _('das Schiff den Traktorstrahl aktiviert hat');
            return false;
        }

        if ($spacecraft instanceof ShipInterface) {
            if ($spacecraft->isTractored()) {
                $reason = _('das Schiff von einem Traktorstrahl gehalten wird');
                return false;
            }
            if ($spacecraft->getDockedTo() !== null) {
                $reason = _('das Schiff angedockt ist');
                return false;
            }
        }

        if ($spacecraft->getAlertState() == SpacecraftAlertStateEnum::ALERT_RED) {
            $reason = _('die Alarmstufe Rot ist');
            return false;
        }


        if ($spacecraft->getWarpDriveState()) {
            $reason = _('das Schiff im Warpantrieb ist');
            return false;
        }

        if (!$spacecraft->getNbs()) {
            $reason = _('die Nahbereichssensoren nicht aktiv sind');
            return false;
        }

        if ($spacecraft->isShielded()) {
            $reason = _('die Schilde aktiviert sind');
            return false;
        }

        if ($spacecraft->isCloaked()) {
            $reason = _('das Schiff getarnt ist');
            return false;
        }

        return true;
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
        $this->spacecraftStateChanger->changeState($wrapper, SpacecraftStateEnum::NONE);

        $spacecraft->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_ON);
    }

    #[Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        $wrapper->get()->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_OFF);
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 15;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 10;
    }
}
