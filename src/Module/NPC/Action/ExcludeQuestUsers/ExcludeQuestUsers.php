<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action\ExcludeQuestUsers;

use Override;
use request;
use Stu\Component\Quest\QuestUserModeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\NPC\View\ShowNPCQuests\ShowNPCQuests;
use Stu\Orm\Repository\NPCQuestLogRepositoryInterface;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ExcludeQuestUsers implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EXCLUDE_QUEST_USERS';

    public function __construct(
        private NPCQuestRepositoryInterface $npcQuestRepository,
        private NPCQuestUserRepositoryInterface $npcQuestUserRepository,
        private UserRepositoryInterface $userRepository,
        private NPCQuestLogRepositoryInterface $npcQuestLogRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowNPCQuests::VIEW_IDENTIFIER);

        $questId = request::postInt('quest_id');
        $userIdsString = trim(request::postString('user_ids') ?: '');

        if ($questId === 0) {
            $game->getInfo()->addInformation('Ungültige Quest-ID');
            return;
        }

        if (empty($userIdsString)) {
            $game->getInfo()->addInformation('Keine User-IDs angegeben');
            return;
        }

        $quest = $this->npcQuestRepository->find($questId);

        if ($quest === null || $quest->getUserId() !== $game->getUser()->getId()) {
            $game->getInfo()->addInformation('Du bist nicht der Ersteller dieser Quest');
            return;
        }

        $userIds = array_map('intval', explode(',', $userIdsString));
        $userIds = array_filter($userIds, fn($id) => $id > 0);

        if (empty($userIds)) {
            $game->getInfo()->addInformation('Keine gültigen User-IDs gefunden');
            return;
        }

        $excludedCount = 0;
        $excludedUsers = [];

        foreach ($userIds as $userId) {
            $user = $this->userRepository->find($userId);

            if ($user === null) {
                continue;
            }

            $existingQuestUser = $this->npcQuestUserRepository->findOneBy([
                'quest_id' => $questId,
                'user_id' => $userId
            ]);

            if ($existingQuestUser !== null) {
                $existingQuestUser->setMode(QuestUserModeEnum::REJECTED_EXCLUDED);
                $this->npcQuestUserRepository->save($existingQuestUser);
                $excludedCount++;
                $excludedUsers[] = $user;
            } else {
                $questUser = $this->npcQuestUserRepository->prototype();
                $questUser->setQuestId($questId);
                $questUser->setQuest($quest);
                $questUser->setUserId($userId);
                $questUser->setUser($user);
                $questUser->setMode(QuestUserModeEnum::REJECTED_EXCLUDED);

                $this->npcQuestUserRepository->save($questUser);
                $excludedCount++;
                $excludedUsers[] = $user;
            }
        }

        if ($excludedCount > 0) {
            foreach ($excludedUsers as $excludedUser) {
                $logEntry = $this->npcQuestLogRepository->prototype();
                $logEntry->setQuestId($questId);
                $logEntry->setQuest($quest);
                $logEntry->setUserId($game->getUser()->getId());
                $logEntry->setUser($game->getUser());
                $logEntry->setMode(1);
                $logEntry->setDate(time());
                $logEntry->setText(sprintf(
                    'Spieler %s (ID: %d) wurde von der Quest "%s" (ID: %d) ausgeschlossen',
                    $excludedUser->getName(),
                    $excludedUser->getId(),
                    $quest->getTitle(),
                    $quest->getId()
                ));
                $this->npcQuestLogRepository->save($logEntry);
            }
            $game->getInfo()->addInformation(sprintf('%d User wurden von der Quest ausgeschlossen', $excludedCount));
        } else {
            $game->getInfo()->addInformation('Keine User wurden ausgeschlossen');
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
