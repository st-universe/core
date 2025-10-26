<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;

class WarpdriveLeakEffectHandler implements EffectHandlerInterface
{
    #[\Override]
    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        // not needed
    }

    #[\Override]
    public function addFlightInformation(Location $location, MessageCollectionInterface $messages): void
    {
        $messages->addInformationf(
            "[color=yellow]Fluktuationen im Warpplasmaleitungssystem durch %s in Sektor %s festgestellt[/color]",
            $location->getFieldType()->getName(),
            $location->getSectorString()
        );
    }

    #[\Override]
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

        $messages->addMessageBy(
            sprintf(
                "%s: [color=yellow]Warpantriebs-Leckage verursacht Kapazitätsverlust von %s Cochrane[/color]",
                $spacecraft->getName(),
                $loss
            ),
            $wrapper->get()->getUser()->getId()
        );
    }
}
