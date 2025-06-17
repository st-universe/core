<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class DeleteBuildplan implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_BUILDPLAN';

    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private NPCLogRepositoryInterface $npcLogRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $buildplanId = request::postIntFatal('planid');
        if ($buildplanId == null) {
            $game->addInformation('Es wurde kein Bauplan ausgewählt');
            return;
        }

        $buildplan = $this->spacecraftBuildplanRepository->find($buildplanId);
        if ($buildplan === null || $buildplan->getUserId() !== $userId) {
            $game->addInformation('Der Bauplan konnte nicht gelöscht werden');
            return;
        }

        $crewCount = $buildplan->getCrew();


        $this->spacecraftBuildplanRepository->delete($buildplan);

        $logText = sprintf(
            '%s hat den Bauplan %s (%d) von Benutzer %s (%d) gelöscht. Crew: %d',
            $game->getUser()->getName(),
            $buildplan->getName(),
            $buildplan->getId(),
            $buildplan->getUser()->getName(),
            $buildplan->getUserId(),
            $crewCount
        );
        if ($game->getUser()->isNpc()) {
            $this->createLogEntry($logText, $userId);
        }

        $game->addInformation('Der Bauplan wurde gelöscht');
    }

    private function createLogEntry(string $text, int $userId): void
    {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($userId);
        $entry->setDate(time());

        $this->npcLogRepository->save($entry);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
