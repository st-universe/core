<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsTakeOffer;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DealsTakeOfferRequest implements DealsTakeOfferRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getDealId(): int
    {
        return $this->parameter('dealid')->int()->required();
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->parameter('amount')->int()->defaultsTo(0);
    }
}
