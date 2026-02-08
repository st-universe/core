<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class AlertStateConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
{
    public function __construct(
        private ActivatorDeactivatorHelperInterface $activatorDeactivatorHelper,
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

        if (!$flightRoute->getNextWaypoint()->hasAnomaly(AnomalyTypeEnum::ION_STORM)) {
            return;
        }

        $spacecraft = $wrapper->get();
        $shieldSystem = $spacecraft->getSystems()[SpacecraftSystemTypeEnum::SHIELDS->value] ?? null;

        if (
            $shieldSystem === null
            || $shieldSystem->getMode()->isActivated()
            || $wrapper->isUnalerted()
        ) {
            return;
        }

        $message = $this->messageFactory->createMessage();
        $messages->add($message);

        $this->activatorDeactivatorHelper->activate($wrapper, SpacecraftSystemTypeEnum::SHIELDS, $message);
    }
}
