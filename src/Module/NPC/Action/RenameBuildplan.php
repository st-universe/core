<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use Override;
use request;
use Stu\Exception\AccessViolation;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class RenameBuildplan implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_BUILDPLAN_NAME';

    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private NPCLogRepositoryInterface $npcLogRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'));
            return;
        }

        $buildplanId = request::postIntFatal('planid');
        $newName = CleanTextUtils::clearEmojis(request::postStringFatal('newName'));

        if (mb_strlen($newName) === 0) {
            return;
        }

        $nameWithoutUnicode = CleanTextUtils::clearUnicode($newName);
        if ($newName !== $nameWithoutUnicode) {
            $game->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($newName) > 255) {
            $game->addInformation(_('Der Name ist zu lang (Maximum: 255 Zeichen)'));
            return;
        }

        $plan = $this->spacecraftBuildplanRepository->find($buildplanId);
        if ($plan === null || $plan->getUserId() !== $userId) {
            throw new AccessViolation();
        }

        $oldName = $plan->getName();
        $plan->setName($newName);

        $this->spacecraftBuildplanRepository->save($plan);

        if ($game->getUser()->isNpc()) {
            $this->createLogEntry($oldName, $newName, $userId, $game->getUser()->getName(), $plan->getUser()->getName());
        }

        $game->addInformation(_('Der Name des Bauplans wurde geändert'));
    }

    private function createLogEntry(string $oldName, string $newName, int $userId, string $userName, string $planuser): void
    {
        $logText = sprintf(
            '%s hat den Bauplan %s des Spielers %s zu %s umbenannt.',
            $userName,
            $oldName,
            $planuser,
            $newName
        );

        $entry = $this->npcLogRepository->prototype();
        $entry->setText($logText);
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
