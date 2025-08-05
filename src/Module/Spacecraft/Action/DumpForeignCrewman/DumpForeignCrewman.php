<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DumpForeignCrewman;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

final class DumpForeignCrewman implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DUMP_CREWMAN';

    private LoggerUtilInterface $loggerUtil;

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private SpacecraftLeaverInterface $spacecraftLeaver,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        //$this->loggerUtil->init('stu', LogLevelEnum::ERROR);
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $this->loggerUtil->log('A');

        $ship = $this->spacecraftLoader->getByIdAndUser(
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

        if ($shipCrew->getSpacecraft() !== $ship) {
            return;
        }

        $name = $shipCrew->getCrew()->getName();

        $survivalMessage = $this->spacecraftLeaver->dumpCrewman(
            $shipCrew,
            sprintf(
                'Die Dienste von Crewman %s werden nicht mehr auf der Station %s von Spieler %s benötigt.',
                $name,
                $ship->getName(),
                $game->getUser()->getName(),
            )
        );

        $game->getInfo()->addInformation($survivalMessage);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
