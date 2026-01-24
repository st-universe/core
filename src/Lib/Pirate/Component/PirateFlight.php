<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class PirateFlight implements PirateFlightInterface
{
    public function __construct(
        private ShipMoverInterface $shipMover,
        private DistributedMessageSenderInterface $distributedMessageSender
    ) {}

    #[\Override]
    public function movePirate(SpacecraftWrapperInterface $wrapper, FlightRouteInterface $flightRoute): void
    {
        $messages = $this->shipMover->checkAndMove(
            $wrapper,
            $flightRoute
        );

        $this->distributedMessageSender->distributeMessageCollection(
            $messages,
            UserConstants::USER_NPC_KAZON,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }
}
