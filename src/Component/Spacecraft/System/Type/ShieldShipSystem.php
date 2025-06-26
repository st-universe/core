<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;

final class ShieldShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public function __construct(private SpacecraftStateChangerInterface $spacecraftStateChanger) {}

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::SHIELDS;
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if ($spacecraft->isCloaked()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        if ($spacecraft->isTractoring()) {
            $reason = _('der Traktorstrahl aktiviert ist');
            return false;
        }

        if ($spacecraft instanceof Ship && $spacecraft->isTractored()) {
            $reason = _('das Schiff von einem Traktorstrahl gehalten wird');
            return false;
        }

        if ($spacecraft->getCondition()->getShield() === 0) {
            $reason = _('die Schildemitter erschöpft sind');
            return false;
        }

        $location = $spacecraft->getLocation();

        $fieldType = $location->getFieldType();
        if ($location->getFieldType()->hasEffect(FieldTypeEffectEnum::SHIELD_MALFUNCTION)) {
            $reason = sprintf('"%s" es verhindert', $fieldType->getName());
            return false;
        }

        if ($location->hasAnomaly(AnomalyTypeEnum::SUBSPACE_ELLIPSE)) {
            $reason = _('in diesem Sektor eine Subraumellipse vorhanden ist');
            return false;
        }

        if ($location->hasAnomaly(AnomalyTypeEnum::ION_STORM)) {
            $reason = _('in diesem Sektor ein Ionensturm tobt');
            return false;
        }

        return true;
    }

    #[Override]
    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void
    {
        $spacecraft = $wrapper->get();
        $this->spacecraftStateChanger->changeState($wrapper, SpacecraftStateEnum::NONE);
        $spacecraft->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_ON);

        if ($spacecraft instanceof Ship) {
            $spacecraft->setDockedTo(null);
        }
    }

    #[Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        $wrapper->get()->getCondition()->setShield(0);
    }

    #[Override]
    public function handleDamage(SpacecraftWrapperInterface $wrapper): void
    {
        $spacecraft = $wrapper->get();
        if ($spacecraft->getCondition()->getShield() > $spacecraft->getMaxShield()) {
            $spacecraft->getCondition()->setShield($spacecraft->getMaxShield());
        }
    }
}
