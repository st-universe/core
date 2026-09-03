<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\AddShoutBoxEntry;

interface AddShoutBoxEntryRequestInterface
{
    public function getTradeNetworkId(): int;

    public function getMessage(): string;
}
