<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Lib;

interface NpcLogTradeMessageLoggerInterface
{
    public function logIfNpcInvolved(int $senderId, int $recipientId, string $text): void;
}
