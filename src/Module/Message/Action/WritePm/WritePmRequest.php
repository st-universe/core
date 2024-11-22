<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\WritePm;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class WritePmRequest implements WritePmRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getRecipientId(): int
    {
        return $this->parameter('recipient')->int()->defaultsTo(0);
    }

    #[Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->parameter('text')->string()->trim()->required()
        );
    }

    #[Override]
    public function getReplyPmId(): int
    {
        return $this->parameter('recipient')->int()->defaultsTo(0);
    }
}
