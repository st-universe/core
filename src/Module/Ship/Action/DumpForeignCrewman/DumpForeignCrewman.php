<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DumpForeignCrewman;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLeaverInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class DumpForeignCrewman implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DUMP_CREWMAN';

    private ShipLoaderInterface $shipLoader;

    private ShipLeaverInterface $shipLeaver;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipLeaverInterface $shipLeaver,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipLeaver = $shipLeaver;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $shipCrewId = request::getIntFatal('scid');

        $shipCrew = $ship->getCrewlist()->get($shipCrewId);

        if ($shipCrew === null) {
            return;
        }

        $name = $shipCrew->getCrew()->getName();

        $msg = $this->shipLeaver->dumpCrewman($shipCrew);

        $this->privateMessageSender->send(
            $userId,
            $shipCrew->getCrew()->getUser()->getId(),
            sprintf(
                'Die Dienste von Crewman %s werden nicht mehr auf der Station %s von Spieler %s benötigt. %s',
                $name,
                $ship->getName(),
                $game->getUser()->getUserName(),
                $msg
            ),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
        );

        $game->addInformation($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
