<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class LogPlayerDetails implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_LOG_PLAYER_DETAILS';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private NPCLogRepositoryInterface $npcLogRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = request::postIntFatal('userId');
        $reason = request::postStringFatal('reason');

        if (!$game->isAdmin() && !$game->isNpc()) {
            return;
        }

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            return;
        }

        $logText = sprintf(
            '%s hat die Details von Spieler %s (%d) eingesehen. Grund: %s',
            $game->getUser()->getName(),
            $user->getName(),
            $user->getId(),
            $reason
        );

        if ($game->getUser()->isNpc()) {
            $this->createLogEntry($logText, $game->getUser()->getId());
        }
    }

    public function createLogEntry(string $text, int $userId): void
    {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($userId);
        $entry->setDate(time());

        $this->npcLogRepository->save($entry);
        $this->entityManager->flush();
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}