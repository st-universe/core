<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\ChangeTradePostName;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeTradePostNameRequest implements ChangeTradePostNameRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getTradePostId(): int
    {
        return $this->parameter('posts_id')->int()->required();
    }

    #[\Override]
    public function getNewName(): string
    {
        return trim(strip_tags($this->parameter('newtradepostname')->string()->defaultsToIfEmpty('')));
    }
}
