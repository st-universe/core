<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\AddShoutBoxEntry;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class AddShoutBoxEntryRequest implements AddShoutBoxEntryRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getTradeNetworkId(): int
    {
        return $this->queryParameter('network')->int()->required();
    }

    #[Override]
    public function getMessage(): string
    {
        return $this->queryParameter('shoutboxentry')->string()->defaultsToIfEmpty('');
    }
}
