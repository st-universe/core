<?php

namespace Stu\Module\Tick\Pirate\Component;

use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class PirateFlight implements PirateFlightInterface
{
    private ShipMoverInterface $shipMover;

    private DistributedMessageSenderInterface $distributedMessageSender;

    public function __construct(
        ShipMoverInterface $shipMover,
        DistributedMessageSenderInterface $distributedMessageSender
    ) {
        $this->shipMover = $shipMover;
        $this->distributedMessageSender = $distributedMessageSender;
    }

    public function movePirate(ShipWrapperInterface $wrapper, FlightRouteInterface $flightRoute): void
    {
        $messages = $this->shipMover->checkAndMove(
            $wrapper,
            $flightRoute
        );

        $this->distributedMessageSender->distributeMessageCollection(
            $messages,
            UserEnum::USER_NPC_KAZON,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );
    }
}
