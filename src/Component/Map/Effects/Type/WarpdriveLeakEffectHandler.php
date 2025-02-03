<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class WarpdriveLeakEffectHandler implements EffectHandlerInterface
{
    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        // not needed
    }

    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        $spacecraft = $wrapper->get();

        $warpdrive = $wrapper->getWarpDriveSystemData();
        if (
            $warpdrive === null
            || !$spacecraft->getWarpDriveState()
        ) {
            return;
        }

        $loss = min($warpdrive->getWarpDrive(), (int)ceil($warpdrive->getTheoreticalMaxWarpdrive() / 10));
        if ($loss === 0) {
            return;
        }

        $warpdrive->lowerWarpDrive($loss)->update();

        $messages->addInformation(
            sprintf("[color=yellow]Leck im Warpantrieb durch %s. (Verlust: %d)[/color]", $spacecraft->getLocation()->getFieldType()->getName(), $loss),
            $wrapper->get()->getUser()->getId()
        );
    }
}
