<?php

namespace Stu\Module\Communication\View\ShowWritePm;

interface ShowWritePmRequestInterface
{
    public function getRecipientId(): int;

    public function getReplyPmId(): int;
}