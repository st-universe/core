<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameBoard;

interface RenameBoardRequestInterface
{
    public function getBoardId(): int;

    public function getTitle(): string;
}
