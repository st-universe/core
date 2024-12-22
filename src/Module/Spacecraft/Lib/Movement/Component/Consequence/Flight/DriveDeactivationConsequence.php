<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class DriveDeactivationConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
{
    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private MessageFactoryInterface $messageFactory
    ) {}


    #[Override]
    protected function skipWhenTractored(): bool
    {
        return true;
    }

    #[Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        if (!$flightRoute->isWarpDriveNeeded()) {
            $this->deactivateSystem(
                $wrapper,
                SpacecraftSystemTypeEnum::WARPDRIVE,
                $messages
            );
        }

        if (!$flightRoute->isImpulseDriveNeeded()) {
            $this->deactivateSystem(
                $wrapper,
                SpacecraftSystemTypeEnum::IMPULSEDRIVE,
                $messages
            );
        }
    }

    private function deactivateSystem(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemTypeEnum $systemType,
        MessageCollectionInterface $messages
    ): void {
        $ship = $wrapper->get();

        if (!$ship->hasShipSystem($systemType)) {
            return;
        }

        if (!$ship->getSystemState($systemType)) {
            return;
        }

        $message = $this->messageFactory->createMessage();
        $messages->add($message);

        $this->spacecraftSystemManager->deactivate($wrapper, $systemType, true);
        $message->add(sprintf(
            _('Die %s deaktiviert %s %s'),
            $ship->getName(),
            $systemType === SpacecraftSystemTypeEnum::TRANSWARP_COIL ? 'die' : 'den',
            $systemType->getDescription()
        ));
    }
}
