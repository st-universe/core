<?php

namespace Stu\Module\Message\Action\WritePm;

interface WritePmRequestInterface
{
    public function getRecipientId(): int;

    public function getText(): string;
}
