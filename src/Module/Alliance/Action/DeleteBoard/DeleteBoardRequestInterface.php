<?php

namespace Stu\Module\Alliance\Action\DeleteBoard;

interface DeleteBoardRequestInterface
{
    public function getBoardId(): int;
}