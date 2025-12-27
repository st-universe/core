<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use InvalidArgumentException;
use request;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class CreateCrew implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_CREW';

    public function __construct(
        private CrewCreatorInterface $crewCreator,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ColonyRepositoryInterface $colonyRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $spacecraftId = request::postInt('spacecraft_id');
        $colonyId = request::postInt('colony_id');
        $amount = request::postIntFatal('crew_amount');

        if ($amount <= 0) {
            $game->getInfo()->addInformation('Anzahl muss größer als 0 sein');
            return;
        }

        if ($spacecraftId > 0 && $colonyId > 0) {
            $game->getInfo()->addInformation('Nur Spacecraft-ID ODER Kolonie-ID angeben');
            return;
        }

        if ($spacecraftId === 0 && $colonyId === 0) {
            $game->getInfo()->addInformation('Spacecraft-ID oder Kolonie-ID angeben');
            return;
        }

        if ($spacecraftId > 0) {
            $this->createCrewForSpacecraft($spacecraftId, $amount, $game);
        } else {
            $this->createCrewForColony($colonyId, $amount, $game);
        }
    }

    private function createCrewForSpacecraft(int $spacecraftId, int $amount, GameControllerInterface $game): void
    {
        $spacecraft = $this->spacecraftRepository->find($spacecraftId);
        if ($spacecraft === null) {
            throw new InvalidArgumentException(sprintf('spacecraftId %d does not exist', $spacecraftId));
        }

        $user = $spacecraft->getUser();

        for ($i = 0; $i < $amount; $i++) {
            $crewAssignment = $this->crewCreator->create($user->getId());

            $crewAssignment->setSpacecraft($spacecraft);
            $crewAssignment->setColony(null);
            $crewAssignment->setTradepost(null);

            $spacecraft->getCrewAssignments()->add($crewAssignment);
        }

        $game->getInfo()->addInformation(sprintf(
            '%d Crew wurde für das Raumschiff %s (ID: %d) von Spieler %s erstellt',
            $amount,
            $spacecraft->getName(),
            $spacecraftId,
            $user->getName()
        ));
    }

    private function createCrewForColony(int $colonyId, int $amount, GameControllerInterface $game): void
    {
        $colony = $this->colonyRepository->find($colonyId);
        if ($colony === null) {
            throw new InvalidArgumentException(sprintf('colonyId %d does not exist', $colonyId));
        }

        $user = $colony->getUser();

        for ($i = 0; $i < $amount; $i++) {
            $this->crewCreator->create($user->getId(), $colony);
        }

        $game->getInfo()->addInformation(sprintf(
            '%d Crew wurde für die Kolonie %s (ID: %d) von Spieler %s erstellt',
            $amount,
            $colony->getName(),
            $colonyId,
            $user->getName()
        ));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
