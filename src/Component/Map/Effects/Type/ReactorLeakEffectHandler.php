<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;

class ReactorLeakEffectHandler implements EffectHandlerInterface
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
            "[color=yellow]Partikelanomalie durch %s kann in Sektor %s hochenergetische Reaktor-Plasmaströme aus der Reaktorkernmatrix extrahieren[/color]",
            $location->getFieldType()->getName(),
            $location->getSectorString()
        );
    }

    #[Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        $spacecraft = $wrapper->get();

        $reactorWrapper = $wrapper->getReactorWrapper();
        if ($reactorWrapper === null) {
            return;
        }

        $loss = min(
            $reactorWrapper->getLoad(),
            $this->stuRandom->rand(
                1,
                (int)ceil($reactorWrapper->getCapacity() / 10),
                true,
                (int)ceil($reactorWrapper->getCapacity() / 20),
            )
        );
        if ($loss === 0) {
            return;
        }

        $reactorWrapper->changeLoad(-$loss);

        $messages->addMessageBy(
            sprintf(
                "%s: [color=yellow]Reaktorladung sinkt signifikant um %d Einheiten[/color]",
                $spacecraft->getName(),
                $loss
            ),
            $wrapper->get()->getUser()->getId()
        );
    }
}