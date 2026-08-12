<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\WritePm;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class WritePmRequest implements WritePmRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getRecipientId(): int
    {
        return $this->parameter('recipient')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->parameter('text')->string()->trim()->required()
        );
    }

    #[\Override]
    public function getQuickPmSourceId(): int
    {
        return $this->parameter('quickPmSourceId')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getQuickPmSourceType(): int
    {
        return $this->parameter('quickPmSourceType')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getQuickPmTargetId(): int
    {
        return $this->parameter('quickPmTargetId')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getQuickPmTargetType(): int
    {
        return $this->parameter('quickPmTargetType')->int()->defaultsTo(0);
    }
}
