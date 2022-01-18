<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DumpForeignCrewman;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLeaverInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

final class DumpForeignCrewman implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DUMP_CREWMAN';

    private ShipLoaderInterface $shipLoader;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ShipLeaverInterface $shipLeaver;

    private PrivateMessageSenderInterface $privateMessageSender;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ShipLeaverInterface $shipLeaver,
        PrivateMessageSenderInterface $privateMessageSender,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->shipLeaver = $shipLeaver;
        $this->privateMessageSender = $privateMessageSender;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        //$this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $this->loggerUtil->log('A');

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $this->loggerUtil->log('B');

        $shipCrewId = request::getIntFatal('scid');

        $shipCrew = $this->shipCrewRepository->find($shipCrewId);

        $this->loggerUtil->log('C');

        if ($shipCrew === null) {
            $this->loggerUtil->log('D');
            return;
        }

        $this->loggerUtil->log('E');

        if ($shipCrew->getShip() !== $ship) {
            return;
        }

        $name = $shipCrew->getCrew()->getName();

        $msg = $this->shipLeaver->dumpCrewman($shipCrew->getId());

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
