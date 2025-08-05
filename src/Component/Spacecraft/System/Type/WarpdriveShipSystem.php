<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use BadMethodCallException;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use Stu\Component\Spacecraft\Event\WarpdriveActivationEvent;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;

final class WarpdriveShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::WARPDRIVE;
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if ($spacecraft instanceof Ship && $spacecraft->isTractored()) {
            $reason = _('es von einem Traktorstrahl gehalten wird');
            return false;
        }

        if ($spacecraft->getHoldingWeb() !== null && $spacecraft->getHoldingWeb()->isFinished()) {
            $reason = _('es in einem Energienetz gefangen ist');
            return false;
        }

        if ($spacecraft->getSystem() !== null && $spacecraft->getSystem()->isWormhole()) {
            $reason = _('es sich in einem Wurmloch befindet');
            return false;
        }

        $reactor = $wrapper->getReactorWrapper();
        if ($reactor === null) {
            throw new BadMethodCallException('this should not happen, warpdrive should only be installed with potent reactor');
        }

        if (!$reactor->isHealthy()) {
            $reason = sprintf(_('der %s zerstört ist'), $reactor->get()->getSystemType()->getDescription());
            return false;
        }

        return true;
    }

    #[Override]
    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void
    {
        $spacecraft = $wrapper->get();
        if ($spacecraft instanceof Ship) {
            $spacecraft->setDockedTo(null);
        }
        $spacecraft->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_ON);

        $this->eventDispatcher->dispatch(new WarpdriveActivationEvent($wrapper));
    }

    #[Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        $systemData = $wrapper->getWarpDriveSystemData();
        if ($systemData === null) {
            throw new BadMethodCallException('this should not happen');
        }

        $systemData->setWarpDrive(0)->update();
    }

    #[Override]
    public function handleDamage(SpacecraftWrapperInterface $wrapper): void
    {
        $systemData = $wrapper->getWarpDriveSystemData();
        if ($systemData === null) {
            throw new BadMethodCallException('this should not happen');
        }

        if ($systemData->getWarpDrive() > $systemData->getMaxWarpDrive()) {
            $systemData->setWarpDrive($systemData->getMaxWarpDrive())->update();
        }
    }
}
