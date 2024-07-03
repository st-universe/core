<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWritePm;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowWritePmRequest implements ShowWritePmRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getRecipientId(): int
    {
        return $this->queryParameter('recipient')->int()->defaultsTo(0);
    }

    #[Override]
    public function getReplyPmId(): int
    {
        return $this->queryParameter('reply')->int()->defaultsTo(0);
    }
}
