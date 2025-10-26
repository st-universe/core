<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class AstroMappingConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
{
    public function __construct(
        private AstroEntryLibInterface $astroEntryLib,
        private MessageFactoryInterface $messageFactory
    ) {}


    #[\Override]
    protected function skipWhenTractored(): bool
    {
        return false;
    }

    #[\Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        if ($ship->getState() === SpacecraftStateEnum::ASTRO_FINALIZING) {
            $message = $this->messageFactory->createMessage(
                null,
                $ship->getUser()->getId(),
                [sprintf('Die %s hat die Kartographierung abgebrochen', $ship->getName())]
            );
            $messages->add($message);

            $this->astroEntryLib->cancelAstroFinalizing($wrapper);
        }
    }
}
