<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteBoard;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteBoardRequest implements DeleteBoardRequestInterface
{
    use CustomControllerHelperTrait;

    public function getBoardId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }
}