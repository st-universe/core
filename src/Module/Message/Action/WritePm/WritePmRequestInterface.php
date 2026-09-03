<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\WritePm;

interface WritePmRequestInterface
{
    public function getRecipientId(): int;

    public function getText(): string;

    public function getQuickPmSourceId(): int;

    public function getQuickPmSourceType(): int;

    public function getQuickPmTargetId(): int;

    public function getQuickPmTargetType(): int;
}
