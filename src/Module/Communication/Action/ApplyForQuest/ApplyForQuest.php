<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\ApplyForQuest;

use Override;
use Stu\Component\Quest\QuestUserModeEnum;
use Stu\Module\Communication\Lib\PlotMemberServiceInterface;
use Stu\Module\Communication\View\ShowQuest\ShowQuest;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\NPCQuestUser;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;

final class ApplyForQuest implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_APPLY_FOR_QUEST';

    public function __construct(
        private ApplyForQuestRequestInterface $applyForQuestRequest,
        private NPCQuestRepositoryInterface $npcQuestRepository,
        private NPCQuestUserRepositoryInterface $npcQuestUserRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private PlotMemberServiceInterface $plotMemberService
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowQuest::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $questId = $this->applyForQuestRequest->getQuestId();

        $quest = $this->npcQuestRepository->find($questId);
        if ($quest === null) {
            $game->getInfo()->addInformation('Quest nicht gefunden');
            return;
        }

        if ($quest->getEnd() !== null) {
            $game->getInfo()->addInformation('Quest ist bereits beendet');
            return;
        }

        $currentTime = time();
        if ($quest->getApplicationEnd() < $currentTime) {
            $game->getInfo()->addInformation('Anmeldeschluss ist bereits abgelaufen');
            return;
        }

        $userFactionId = $user->getFactionId();
        $allowedFactions = $quest->getFactions();
        if ($allowedFactions !== null && !in_array($userFactionId, $allowedFactions)) {
            $game->getInfo()->addInformation('Deine Fraktion darf sich nicht für diese Quest bewerben');
            return;
        }

        $existingQuestUser = $this->npcQuestUserRepository->findOneBy([
            'quest_id' => $questId,
            'user_id' => $user->getId()
        ]);

        if ($existingQuestUser !== null) {
            if ($existingQuestUser->getMode() === QuestUserModeEnum::ACTIVE_MEMBER) {
                $game->getInfo()->addInformation('Du bist bereits aktiver Teilnehmer dieser Quest');
                return;
            } elseif ($existingQuestUser->getMode() === QuestUserModeEnum::APPLICANT) {
                $game->getInfo()->addInformation('Du hast dich bereits für diese Quest beworben');
                return;
            } elseif ($existingQuestUser->getMode() === QuestUserModeEnum::INVITED) {
                $game->getInfo()->addInformation('Du bist bereits zu dieser Quest eingeladen');
                return;
            } elseif ($existingQuestUser->getMode() === QuestUserModeEnum::REJECTED_EXCLUDED) {
                $game->getInfo()->addInformation('Du wurdest für diese Quest abgelehnt oder ausgeschlossen');
                return;
            }
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

        $questUser = $this->npcQuestUserRepository->prototype();
        $questUser->setQuestId($questId);
        $questUser->setQuest($quest);
        $questUser->setUserId($user->getId());
        $questUser->setUser($user);

        if ($quest->isApprovalRequired()) {
            $questUser->setMode(QuestUserModeEnum::APPLICANT);
            $game->getInfo()->addInformation('Bewerbung erfolgreich eingereicht. Du musst nun auf die Bestätigung durch den Quest-Leiter warten');
            $this->notifyQuestLeader(
                $quest,
                $questUser,
                sprintf(
                    'Der Spieler %s hat sich soeben für deine Quest "%s" beworben',
                    $user->getName(),
                    $quest->getTitle()
                )
            );
        } else {
            $questUser->setMode(QuestUserModeEnum::ACTIVE_MEMBER);
            $this->plotMemberService->addUserToPlotIfExists($quest, $user);
            $this->notifyQuestLeader(
                $quest,
                $questUser,
                sprintf(
                    'Der Spieler %s hat sich soeben für deine Quest "%s" angemeldet',
                    $user->getName(),
                    $quest->getTitle()
                )
            );
            $game->getInfo()->addInformation('Du nimmst ab sofort an der Quest teil!');
        }

        $this->npcQuestUserRepository->save($questUser);
    }

    private function notifyQuestLeader(NPCQuest $quest, NPCQuestUser $questUser, string $text): void
    {
        $quesLeader = $quest->getUser();

        if ($quesLeader != $questUser && $quesLeader !== null) {

            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $quesLeader->getId(),
                $text,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                sprintf('/npc/?SHOW_NPC_QUESTS=1')
            );
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
