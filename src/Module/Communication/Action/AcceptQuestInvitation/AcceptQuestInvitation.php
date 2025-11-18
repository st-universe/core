<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AcceptQuestInvitation;

use Override;
use Stu\Component\Quest\QuestUserModeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowQuest\ShowQuest;
use Stu\Module\Communication\Lib\PlotMemberServiceInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;
use Stu\Orm\Repository\NPCQuestLogRepositoryInterface;

final class AcceptQuestInvitation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACCEPT_QUEST_INVITATION';

    public function __construct(
        private AcceptQuestInvitationRequestInterface $acceptQuestInvitationRequest,
        private NPCQuestRepositoryInterface $npcQuestRepository,
        private NPCQuestUserRepositoryInterface $npcQuestUserRepository,
        private NPCQuestLogRepositoryInterface $npcQuestLogRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private PlotMemberServiceInterface $plotMemberService
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowQuest::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $questId = $this->acceptQuestInvitationRequest->getQuestId();

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
                fn($questUser) => $questUser->getMode() === QuestUserModeEnum::ACTIVE_MEMBER
            ));

            if ($activeMembersCount >= $quest->getApplicantMax()) {
                $game->getInfo()->addInformation('Die maximale Teilnehmerzahl ist bereits erreicht');
                return;
            }
        }

        $questUser->setMode(QuestUserModeEnum::ACTIVE_MEMBER);
        $this->npcQuestUserRepository->save($questUser);
        $this->plotMemberService->addUserToPlotIfExists($quest, $user);
        $logEntry = $this->npcQuestLogRepository->prototype();
        $logEntry->setQuestId($questId);
        $logEntry->setQuest($quest);
        $logEntry->setUserId($game->getUser()->getId());
        $logEntry->setUser($game->getUser());
        $logEntry->setMode(1);
        $logEntry->setDate(time());
        $logEntry->setText(sprintf(
            'Spieler %s (ID: %d) hat die Einladung zur Quest "%s" (ID: %d) angenommen',
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
                'Der Spieler %s hat deine Einladung für die Quest "%s" angenommen',
                $user->getName(),
                $quest->getTitle()
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
            sprintf('/npc/?SHOW_NPC_QUESTS=1')

        );

        $game->getInfo()->addInformation('Du nimmst ab sofort an der Quest teil');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}