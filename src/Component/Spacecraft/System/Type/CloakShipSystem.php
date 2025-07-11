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
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;

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

        if ($spacecraft instanceof Ship && $spacecraft->isTractored()) {
            $reason = _('das Schiff von einem Traktorstrahl gehalten wird');
            return false;
        }

        $fieldType = $wrapper->get()->getLocation()->getFieldType();
        if ($fieldType->hasEffect(FieldTypeEffectEnum::CLOAK_UNUSEABLE)) {
            $reason = sprintf('"%s" es verhindert', $fieldType->getName());
            return false;
        }

        if ($spacecraft->getSubspaceState()) {
            $reason = _('die Subraumfeldsensoren aktiv sind');
            return false;
        }

        if ($wrapper->getAlertState() == SpacecraftAlertStateEnum::ALERT_RED) {
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

        if ($spacecraft instanceof Ship) {
            $spacecraft->setDockedTo(null);
        }
        $this->spacecraftStateChanger->changeState($wrapper, SpacecraftStateEnum::NONE);

        if ($spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::ASTRO_LABORATORY)) {
            $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::ASTRO_LABORATORY)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }
        if ($spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)) {
            $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }
        if ($spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)) {
            $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }
        if ($spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)) {
            $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        }

        $spacecraft->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_ON);
    }
}
