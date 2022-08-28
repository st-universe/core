<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\ChangeTradePostName;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeTradePostNameRequest implements ChangeTradePostNameRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTradePostId(): int
    {
        return $this->queryParameter('posts_id')->int()->required();
    }

    public function getNewName(): string
    {
        return trim(strip_tags($this->queryParameter('newtradepostname')->string()->defaultsToIfEmpty('')));
    }
}