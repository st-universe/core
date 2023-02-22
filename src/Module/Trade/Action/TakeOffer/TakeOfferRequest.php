<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TakeOffer;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class TakeOfferRequest implements TakeOfferRequestInterface
{
    use CustomControllerHelperTrait;

    public function getOfferId(): int
    {
        return $this->queryParameter('offerid')->int()->required();
    }

    public function getAmount(): int
    {
        return $this->queryParameter('amount')->int()->defaultsTo(1);
    }
}
