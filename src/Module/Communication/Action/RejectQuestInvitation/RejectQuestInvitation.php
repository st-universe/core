<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\RejectQuestInvitation;

use Override;
use Stu\Component\Quest\QuestUserModeEnum;
use Stu\Module\Communication\View\ShowQuest\ShowQuest;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\NPCQuestLogRepositoryInterface;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;

final class RejectQuestInvitation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REJECT_QUEST_INVITATION';

    public function __construct(
        private RejectQuestInvitationRequestInterface $rejectQuestInvitationRequest,
        private NPCQuestRepositoryInterface $npcQuestRepository,
        private NPCQuestUserRepositoryInterface $npcQuestUserRepository,
        private NPCQuestLogRepositoryInterface $npcQuestLogRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowQuest::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $questId = $this->rejectQuestInvitationRequest->getQuestId();

        $quest = $this->npcQuestRepository->find($questId);
        if ($quest === null) {
            $game->getInfo()->addInformation('Quest nicht gefunden');
            return;
        }

        if ($quest->getEnd() !== null) {
            $game->getInfo()->addInformation('Quest ist bereits beendet');
            return;
        }

        $questUser = $this->npcQuestUserRepository->findOneBy([
            'quest_id' => $questId,
            'user_id' => $user->getId()
        ]);

        if ($questUser === null) {
            $game->getInfo()->addInformation('Du wurdest nicht zu dieser Quest eingeladen');
            return;
        }

        if ($questUser->getMode() !== QuestUserModeEnum::INVITED) {
            $game->getInfo()->addInformation('Du bist nicht zu dieser Quest eingeladen');
            return;
        }

        if ($quest->getApplicantMax() !== null) {
            $activeMembersCount = count($quest->getQuestUsers()->filter(
                fn ($questUser) => $questUser->getMode() === QuestUserModeEnum::ACTIVE_MEMBER
            ));

            if ($activeMembersCount >= $quest->getApplicantMax()) {
                $game->getInfo()->addInformation('Die maximale Teilnehmerzahl ist bereits erreicht');
                return;
            }
        }

        $questUser->setMode(QuestUserModeEnum::REJECTED_EXCLUDED);
        $this->npcQuestUserRepository->save($questUser);
        $logEntry = $this->npcQuestLogRepository->prototype();
        $logEntry->setQuestId($questId);
        $logEntry->setQuest($quest);
        $logEntry->setUserId($game->getUser()->getId());
        $logEntry->setUser($game->getUser());
        $logEntry->setMode(1);
        $logEntry->setDate(time());
        $logEntry->setText(sprintf(
            'Spieler %s (ID: %d) hat die Einladung zur Quest "%s" (ID: %d) abgelehnt',
            $user->getName(),
            $user->getId(),
            $quest->getTitle(),
            $quest->getId()
        ));
        $this->npcQuestLogRepository->save($logEntry);
        $this->privateMessageSender->send(
            UserConstants::USER_NOONE,
            $quest->getUserId(),
            sprintf(
                'Der Spieler %s hat deine Einladung für die Quest "%s" abgelehnt',
                $user->getName(),
                $quest->getTitle()
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
            sprintf('/npc/?SHOW_NPC_QUESTS=1')
        );

        $game->getInfo()->addInformation('Du hast die Einladung an der Quest abgelehnt');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
