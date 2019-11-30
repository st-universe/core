<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWriteQuickPm;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowWriteQuickPmRequest implements ShowWriteQuickPmRequestInterface
{
    use CustomControllerHelperTrait;

    public function getRecipientId(): int
    {
        return $this->queryParameter('recipient')->int()->required();
    }
}
