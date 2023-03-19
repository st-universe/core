<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Board;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class BoardRequest implements BoardRequestInterface
{
    use CustomControllerHelperTrait;

    public function getBoardId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }
}
