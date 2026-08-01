<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DumpForeignCrewman;

use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
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
        private SpacecraftLeaverInterface $spacecraftLeaver,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true
        );

        $crewId = request::getIntFatal('crewid');
        $crewAssignment = $this->crewAssignmentRepository->find($crewId);
        if ($crewAssignment === null) {
            return;
        }

        if ($crewAssignment->getSpacecraft()?->getId() !== $ship->getId()) {
            return;
        }

        $crew = $crewAssignment->getCrew();
        $crewOwnerId = $crew->getUser()->getId();
        $shipOwnerId = $ship->getUser()->getId();

        if ($shipOwnerId === $userId) {
            if ($crewOwnerId === $userId) {
                return;
            }

            $survivalMessage = $this->spacecraftLeaver->dumpCrewman(
                $crewAssignment,
                sprintf(
                    'Die Dienste von Crewman %s werden nicht mehr auf der Station %s von Spieler %s benötigt.',
                    $crew->getName(),
                    $ship->getName(),
                    $user->getName(),
                )
            );
        } elseif ($crewOwnerId === $userId) {
            $survivalMessage = $this->spacecraftLeaver->leaveSpacecraft($crewAssignment);

            $this->privateMessageSender->send(
                $userId,
                $shipOwnerId,
                sprintf(
                    'Spieler %s hat seinen Crewman %s von der Station %s entfernt.',
                    $user->getName(),
                    $crew->getName(),
                    $ship->getName(),
                )
            );
        } else {
            throw new AccessViolationException();
        }

        $game->getInfo()->addInformation($survivalMessage);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
