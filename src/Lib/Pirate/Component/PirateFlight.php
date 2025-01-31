<?php

namespace Stu\Lib\Pirate\Component;

use Override;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class PirateFlight implements PirateFlightInterface
{
    public function __construct(
        private ShipMoverInterface $shipMover,
        private DistributedMessageSenderInterface $distributedMessageSender
    ) {}

    #[Override]
    public function movePirate(ShipWrapperInterface $wrapper, FlightRouteInterface $flightRoute): void
    {
        $messages = $this->shipMover->checkAndMove(
            $wrapper,
            $flightRoute
        );

        $this->distributedMessageSender->distributeMessageCollection(
            $messages,
            UserEnum::USER_NPC_KAZON,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }
}
