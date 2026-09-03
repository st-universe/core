<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AddBoard;

interface AddBoardRequestInterface
{
    public function getBoardName(): string;
}
