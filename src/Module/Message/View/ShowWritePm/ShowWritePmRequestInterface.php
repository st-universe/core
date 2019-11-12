<?php

namespace Stu\Module\Message\View\ShowWritePm;

interface ShowWritePmRequestInterface
{
    public function getRecipientId(): int;

    public function getReplyPmId(): int;
}
