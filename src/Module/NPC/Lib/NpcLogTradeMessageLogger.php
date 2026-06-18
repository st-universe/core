<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Lib;

use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class NpcLogTradeMessageLogger implements NpcLogTradeMessageLoggerInterface
{
    public function __construct(
        private readonly NPCLogRepositoryInterface $npcLogRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function logIfNpcInvolved(int $senderId, int $recipientId, string $text): void
    {
        if ($senderId === $recipientId) {
            return;
        }

        $npcUserId = $this->getNpcUserId($senderId, $recipientId);
        if ($npcUserId === null) {
            return;
        }

        $sender = $this->userRepository->find($senderId);
        $recipient = $this->userRepository->find($recipientId);
        if ($sender === null || $recipient === null) {
            return;
        }

        $entry = $this->npcLogRepository->prototype();
        $entry->setText(sprintf(
            '%s (%d) -> %s (%d): %s',
            $sender->getName(),
            $senderId,
            $recipient->getName(),
            $recipientId,
            $text
        ));
        $entry->setSourceUserId($npcUserId);
        $entry->setDate(time());
        $entry->setAdminView(true);

        $this->npcLogRepository->save($entry);
    }

    private function getNpcUserId(int $senderId, int $recipientId): ?int
    {
        if ($this->isTradeMessageNpc($senderId)) {
            return $senderId;
        }

        if ($this->isTradeMessageNpc($recipientId)) {
            return $recipientId;
        }

        return null;
    }

    private function isTradeMessageNpc(int $userId): bool
    {
        return $userId >= UserConstants::USER_FIRST_NPC
            && $userId < UserConstants::USER_FIRST_ID;
    }
}
