<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class DriveActivationConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
{
    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[\Override]
    protected function skipWhenTractored(): bool
    {
        return true;
    }

    #[\Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $message = $this->messageFactory->createMessage();
        $messages->add($message);

        if ($flightRoute->isImpulseDriveNeeded()) {
            $this->activate(
                $wrapper,
                SpacecraftSystemTypeEnum::IMPULSEDRIVE,
                $message
            );
        }

        if ($flightRoute->isWarpDriveNeeded()) {
            $this->activate(
                $wrapper,
                SpacecraftSystemTypeEnum::WARPDRIVE,
                $message
            );
        }

        if ($flightRoute->isTranswarpCoilNeeded()) {
            $this->activate(
                $wrapper,
                SpacecraftSystemTypeEnum::TRANSWARP_COIL,
                $message
            );
        }
    }

    private function activate(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemTypeEnum $systemType,
        MessageInterface $message
    ): void {
        $ship = $wrapper->get();

        if (!$ship->getSystemState($systemType)) {
            $this->spacecraftSystemManager->activate($wrapper, $systemType);

            $message->add(sprintf(
                _('Die %s aktiviert %s %s'),
                $ship->getName(),
                $systemType === SpacecraftSystemTypeEnum::TRANSWARP_COIL ? 'die' : 'den',
                $systemType->getDescription()
            ));
        }
    }
}
