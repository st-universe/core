<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AddBoard;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class AddBoardRequest implements AddBoardRequestInterface
{
    use CustomControllerHelperTrait;

    public function getBoardName(): string
    {
        return trim(tidyString(strip_tags(
            $this->queryParameter('board')->string()->defaultsToIfEmpty('')
        )));
    }

}