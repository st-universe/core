<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DumpForeignCrewman;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\Crew\ShipLeaverInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

final class DumpForeignCrewman implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DUMP_CREWMAN';

    private ShipLoaderInterface $shipLoader;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ShipLeaverInterface $shipLeaver;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ShipLeaverInterface $shipLeaver,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->shipLeaver = $shipLeaver;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
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

        $survivalMessage = $this->shipLeaver->dumpCrewman(
            $shipCrew,
            sprintf(
                'Die Dienste von Crewman %s werden nicht mehr auf der Station %s von Spieler %s benötigt.',
                $name,
                $ship->getName(),
                $game->getUser()->getName(),
            )
        );

        $game->addInformation($survivalMessage);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
