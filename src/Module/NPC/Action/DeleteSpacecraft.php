<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

final class DeleteSpacecraft implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_SPACECRAFT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(private SpacecraftLoaderInterface $spacecraftLoader, private NPCLogRepositoryInterface $npcLogRepository, private SpacecraftRemoverInterface $spacecraftRemover, private CrewRepositoryInterface $crewRepository, private CrewAssignmentRepositoryInterface $shipCrewRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTools::VIEW_IDENTIFIER);
        $user = $game->getUser();

        // only Admins or NPC can trigger
        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'));
            return;
        }

        if (!request::getVarByMethod(request::postvars(), 'spacecraftid')) {
            $game->getInfo()->addInformation("Es wurde kein Spacecraft ausgewählt");
            return;
        } else {
            $spacecraftIdInput = request::postString('spacecraftid');
            $reason = request::postString('reason');

            $spacecraftIdInput = $spacecraftIdInput === false ? '' : $spacecraftIdInput;
            $reason = $reason === false ? '' : $reason;

            if ($game->getUser()->isNpc() && $reason === '') {
                $game->getInfo()->addInformation("Grund fehlt");
                return;
            }

            if (!preg_match('/^[\d\s,]+$/', $spacecraftIdInput)) {
                $game->getInfo()->addInformation("Die Spacecraft-ID darf nur Zahlen, Kommas und Leerzeichen enthalten");
                return;
            }

            $spacecraftIds = array_filter(
                array_map(
                    'trim',
                    explode(',', $spacecraftIdInput)
                ),
                function ($id): bool {
                    return is_numeric($id) && $id > 0;
                }
            );

            if ($spacecraftIds === []) {
                $game->getInfo()->addInformation("Es wurden keine gültigen Spacecraft-IDs gefunden");
                return;
            }

            $deletedCount = 0;
            foreach ($spacecraftIds as $spacecraftId) {
                $wrapper = $this->spacecraftLoader->find((int)$spacecraftId);

                if ($wrapper === null) {
                    $game->getInfo()->addInformationf("Spacecraft mit ID %d existiert nicht!", (int)$spacecraftId);
                    continue;
                }

                $spacecraft = $wrapper->get();

                if ($spacecraft->isStation()) {
                    $game->getInfo()->addInformation("Stationen können nicht gelöscht werden");
                    continue;
                }

                $text = sprintf(
                    '%s hat das Spacecraft %s (%d) von Spieler %s (%d) gelöscht. Grund: %s',
                    $user->getName(),
                    $spacecraft->getName(),
                    $spacecraft->getId(),
                    $spacecraft->getUser()->getName(),
                    $spacecraft->getUser()->getId(),
                    $reason
                );

                if ($game->getUser()->isNpc()) {
                    $this->createEntry($text, $user->getId());
                }

                $this->letCrewDie($spacecraft);
                $this->spacecraftRemover->remove($spacecraft);
                $deletedCount++;
            }

            if ($deletedCount > 0) {
                $game->getInfo()->addInformationf("%d Schiff(e) gelöscht", $deletedCount);
            } else {
                $game->getInfo()->addInformation("Es wurden keine Schiffe gelöscht");
            }
        }
    }

    private function createEntry(
        string $text,
        int $UserId
    ): void {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($UserId);
        $entry->setDate(time());

        $this->npcLogRepository->save($entry);
    }

    private function letCrewDie(Spacecraft $spacecraft): void
    {
        $crewArray = [];
        foreach ($spacecraft->getCrewAssignments() as $shipCrew) {
            $crewArray[] = $shipCrew->getCrew();
            $shipCrew->clearAssignment();
            $this->shipCrewRepository->delete($shipCrew);
        }

        foreach ($crewArray as $crew) {
            $this->crewRepository->delete($crew);
        }

        $spacecraft->getCrewAssignments()->clear();
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}