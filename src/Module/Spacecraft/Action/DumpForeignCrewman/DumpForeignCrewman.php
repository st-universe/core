<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DumpForeignCrewman;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

final class DumpForeignCrewman implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DUMP_CREWMAN';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private SpacecraftLeaverInterface $spacecraftLeaver
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $crewId = request::getIntFatal('crewid');
        $crewAssignment = $this->crewAssignmentRepository->find($crewId);
        if ($crewAssignment === null) {
            return;
        }

        if ($crewAssignment->getSpacecraft()?->getId() !== $ship->getId()) {
            return;
        }

        $name = $crewAssignment->getCrew()->getName();

        $survivalMessage = $this->spacecraftLeaver->dumpCrewman(
            $crewAssignment,
            sprintf(
                'Die Dienste von Crewman %s werden nicht mehr auf der Station %s von Spieler %s benötigt.',
                $name,
                $ship->getName(),
                $game->getUser()->getName(),
            )
        );

        $game->getInfo()->addInformation($survivalMessage);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
