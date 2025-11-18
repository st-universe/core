<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action\AddQuestLogEntry;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\NPC\View\ShowNPCQuests\ShowNPCQuests;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\NPCQuestLog;
use Stu\Orm\Repository\NPCQuestLogRepositoryInterface;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;

final class AddQuestLogEntry implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_QUEST_LOG_ENTRY';

    public function __construct(
        private NPCQuestRepositoryInterface $npcQuestRepository,
        private NPCQuestLogRepositoryInterface $npcQuestLogRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowNPCQuests::VIEW_IDENTIFIER);

        $questId = request::postInt('quest_id');
        $text = trim(request::postString('log_text') ?: '');

        if ($questId === 0) {
            return;
        }

        if (mb_strlen($text) < 3) {
            return;
        }

        $quest = $this->npcQuestRepository->find($questId);

        if ($quest === null || $quest->getUserId() !== $game->getUser()->getId()) {
            return;
        }

        $logEntry = $this->npcQuestLogRepository->prototype();
        $logEntry->setQuestId($questId);
        $logEntry->setQuest($quest);
        $logEntry->setUserId($game->getUser()->getId());
        $logEntry->setUser($game->getUser());
        $logEntry->setMode(2);
        $logEntry->setDate(time());
        $logEntry->setText($text);

        $this->npcQuestLogRepository->save($logEntry);

        $this->notifyQuestMembers($quest, $logEntry);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }

    private function notifyQuestMembers(NPCQuest $quest, NPCQuestLog $logEntry): void
    {
        $activeMembers = $quest->getQuestUsers()->filter(
            fn($questUser) => $questUser->getMode()->value === 1
        );

        $questLeaderId = $quest->getUserId();

        foreach ($activeMembers as $questUser) {
            $user = $questUser->getUser();
            if ($user === null || $user->getId() === $questLeaderId) {
                continue;
            }

            $text = sprintf(
                'Neuer Eintrag im Quest-Ticker für Quest "%s"',
                $quest->getTitle()
            );

            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $user->getId(),
                $text,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                sprintf('/comm.php?SHOW_QUEST=1&questid=%d', $quest->getId())
            );
        }
    }
}