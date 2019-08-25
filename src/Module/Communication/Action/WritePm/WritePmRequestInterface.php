<?php

namespace Stu\Module\Communication\Action\WritePm;

interface WritePmRequestInterface
{
    public function getRecipientId(): int;

    public function getText(): string;

    public function getReplyPmId(): int;
}