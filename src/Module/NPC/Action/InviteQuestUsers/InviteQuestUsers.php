<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action\InviteQuestUsers;

use Override;
use request;
use Stu\Component\Quest\QuestUserModeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\NPC\View\ShowNPCQuests\ShowNPCQuests;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class InviteQuestUsers implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_INVITE_QUEST_USERS';

    public function __construct(
        private NPCQuestRepositoryInterface $npcQuestRepository,
        private NPCQuestUserRepositoryInterface $npcQuestUserRepository,
        private UserRepositoryInterface $userRepository
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

        $invitedCount = 0;

        foreach ($userIds as $userId) {
            $user = $this->userRepository->find($userId);

            if ($user === null) {
                continue;
            }

            $existingQuestUser = $this->npcQuestUserRepository->findOneBy([
                'quest_id' => $questId,
                'user_id' => $userId
            ]);

            if ($existingQuestUser === null) {
                $questUser = $this->npcQuestUserRepository->prototype();
                $questUser->setQuestId($questId);
                $questUser->setQuest($quest);
                $questUser->setUserId($userId);
                $questUser->setUser($user);
                $questUser->setMode(QuestUserModeEnum::INVITED);

                $this->npcQuestUserRepository->save($questUser);
                $invitedCount++;
            } else if ($existingQuestUser->getMode() === QuestUserModeEnum::REJECTED_EXCLUDED) {
                $existingQuestUser->setMode(QuestUserModeEnum::INVITED);
                $this->npcQuestUserRepository->save($existingQuestUser);
                $invitedCount++;
            }
        }

        if ($invitedCount > 0) {
            $game->getInfo()->addInformation(sprintf('%d User wurden zur Quest eingeladen', $invitedCount));
        } else {
            $game->getInfo()->addInformation('Keine neuen User wurden eingeladen');
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
