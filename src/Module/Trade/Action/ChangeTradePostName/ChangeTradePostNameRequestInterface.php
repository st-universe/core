<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\ChangeTradePostName;

interface ChangeTradePostNameRequestInterface
{
    public function getTradePostId(): int;

    public function getNewName(): string;
}