<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action\RejectQuestApplication;

use Override;
use request;
use Stu\Component\Quest\QuestUserModeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\NPC\View\ShowNPCQuests\ShowNPCQuests;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\NPCQuestLogRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;

final class RejectQuestApplication implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REJECT_QUEST_APPLICATION';

    public function __construct(
        private NPCQuestUserRepositoryInterface $npcQuestUserRepository,
        private NPCQuestLogRepositoryInterface $npcQuestLogRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowNPCQuests::VIEW_IDENTIFIER);

        $questUserIdParameter = request::postInt('quest_user_id');

        if ($questUserIdParameter === 0) {
            $game->getInfo()->addInformation('Ungültige Quest-User-ID');
            return;
        }

        $questUser = $this->npcQuestUserRepository->find($questUserIdParameter);

        if ($questUser === null) {
            $game->getInfo()->addInformation('Quest-User nicht gefunden');
            return;
        }

        $quest = $questUser->getQuest();
        if ($quest === null || $quest->getUserId() !== $game->getUser()->getId()) {
            $game->getInfo()->addInformation('Du bist nicht der Ersteller dieser Quest');
            return;
        }

        if ($questUser->getMode() !== QuestUserModeEnum::APPLICANT) {
            $game->getInfo()->addInformation('User ist kein Bewerber');
            return;
        }

        $questUser->setMode(QuestUserModeEnum::REJECTED_EXCLUDED);
        $this->npcQuestUserRepository->save($questUser);

        $user = $questUser->getUser();
        if ($user !== null) {
            $logEntry = $this->npcQuestLogRepository->prototype();
            $logEntry->setQuestId($quest->getId());
            $logEntry->setQuest($quest);
            $logEntry->setUserId($game->getUser()->getId());
            $logEntry->setUser($game->getUser());
            $logEntry->setMode(1);
            $logEntry->setDate(time());
            $logEntry->setText(sprintf(
                'Spieler %s (ID: %d) wurde für die Quest "%s" (ID: %d) abgelehnt',
                $user->getName(),
                $user->getId(),
                $quest->getTitle(),
                $quest->getId()
            ));
            $this->npcQuestLogRepository->save($logEntry);
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $user->getId(),
                sprintf(
                    'Deine Bewerbung für die Quest "%s" wurde abgelehnt',
                    $quest->getTitle()
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                sprintf('/comm.php?SHOW_QUEST=1&questid=%d', $quest->getId())
            );
        }

        $game->getInfo()->addInformation('Bewerbung wurde abgelehnt');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
