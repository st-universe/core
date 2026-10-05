<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class DeleteSpacecraft implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_SPACECRAFT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(private SpacecraftLoaderInterface $spacecraftLoader, private NPCLogRepositoryInterface $npcLogRepository, private SpacecraftRemoverInterface $spacecraftRemover, private CrewRepositoryInterface $crewRepository, private CrewAssignmentRepositoryInterface $shipCrewRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowTools::VIEW_IDENTIFIER);
        $user = $context->getUser();
        if (!request::getVarByMethod(request::postvars(), 'spacecraftid')) {
            $context->getInfo()->addInformation("Es wurde kein Spacecraft ausgewählt");
            return;
        }
        $spacecraftIdInput = request::postString('spacecraftid');
        $reason = request::postString('reason');
        $spacecraftIdInput = $spacecraftIdInput === false ? '' : $spacecraftIdInput;
        $reason = $reason === false ? '' : $reason;
        if ($context->getUser()->isNpc() && $reason === '') {
            $context->getInfo()->addInformation("Grund fehlt");
            return;
        }
        if (!preg_match('/^[\d\s,]+$/', $spacecraftIdInput)) {
            $context->getInfo()->addInformation("Die Spacecraft-ID darf nur Zahlen, Kommas und Leerzeichen enthalten");
            return;
        }
        $spacecraftIds = array_filter(
            array_map(
                'trim',
                explode(',', $spacecraftIdInput)
            ),
            fn ($id): bool => is_numeric($id) && $id > 0
        );
        if ($spacecraftIds === []) {
            $context->getInfo()->addInformation("Es wurden keine gültigen Spacecraft-IDs gefunden");
            return;
        }
        $deletedCount = 0;
        foreach ($spacecraftIds as $spacecraftId) {
            $wrapper = $this->spacecraftLoader->find((int)$spacecraftId);

            if ($wrapper === null) {
                $context->getInfo()->addInformationf("Spacecraft mit ID %d existiert nicht!", (int)$spacecraftId);
                continue;
            }

            $spacecraft = $wrapper->get();

            if ($spacecraft->isStation()) {
                $context->getInfo()->addInformation("Stationen können nicht gelöscht werden");
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

            if ($context->getUser()->isNpc()) {
                $this->createEntry($text, $user->getId());
            }

            $this->letCrewDie($spacecraft);
            $this->spacecraftRemover->remove($spacecraft);
            $deletedCount++;
        }
        if ($deletedCount > 0) {
            $context->getInfo()->addInformationf("%d Schiff(e) gelöscht", $deletedCount);
        } else {
            $context->getInfo()->addInformation("Es wurden keine Schiffe gelöscht");
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
        $entry->setAdminView(false);

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
