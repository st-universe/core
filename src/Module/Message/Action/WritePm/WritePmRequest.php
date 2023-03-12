<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\WritePm;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class WritePmRequest implements WritePmRequestInterface
{
    use CustomControllerHelperTrait;

    public function getRecipientId(): int
    {
        return $this->queryParameter('recipient')->int()->defaultsTo(0);
    }

    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('text')->string()->trim()->required()
        );
    }

    public function getReplyPmId(): int
    {
        return $this->queryParameter('recipient')->int()->defaultsTo(0);
    }
}
