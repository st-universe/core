<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TakeOffer;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class TakeOfferRequest implements TakeOfferRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getOfferId(): int
    {
        return $this->queryParameter('offerid')->int()->required();
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->queryParameter('amount')->int()->defaultsTo(1);
    }
}
