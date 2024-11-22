<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameBoard;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class RenameBoardRequest implements RenameBoardRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getBoardId(): int
    {
        return $this->parameter('boardid')->int()->required();
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->tidyString(
            $this->parameter('tname')->string()->defaultsToIfEmpty('')
        );
    }
}
