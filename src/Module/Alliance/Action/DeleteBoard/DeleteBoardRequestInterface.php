<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteBoard;

interface DeleteBoardRequestInterface
{
    public function getBoardId(): int;
}
