<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;

class EpsLeakEffectHandler implements EffectHandlerInterface
{
    public function __construct(private StuRandom $stuRandom) {}

    #[Override]
    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        // not needed
    }

    #[Override]
    public function addFlightInformation(Location $location, MessageCollectionInterface $messages): void
    {
        $messages->addInformationf(
            "[color=yellow]Energetische Disruption durch %s in Sektor %s kann den Plasmastrom von EPS-Systemen absorbieren[/color]",
            $location->getFieldType()->getName(),
            $location->getSectorString()
        );
    }

    #[Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        $spacecraft = $wrapper->get();

        $epsSystemData = $wrapper->getEpsSystemData();
        if ($epsSystemData === null) {
            return;
        }

        $loss = min(
            $epsSystemData->getEps(),
            $this->stuRandom->rand(
                1,
                (int)ceil($epsSystemData->getTheoreticalMaxEps() / 10),
                true,
                (int)ceil($epsSystemData->getTheoreticalMaxEps() / 20),
            )
        );
        if ($loss === 0) {
            return;
        }

        $epsSystemData->lowerEps($loss)->update();

        $messages->addMessageBy(
            sprintf(
                "%s: [color=yellow]%d Energie wird abgeleitet[/color]",
                $spacecraft->getName(),
                $loss
            ),
            $wrapper->get()->getUser()->getId()
        );
    }
}
