<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CancelOffer;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CancelOfferRequest implements CancelOfferRequestInterface
{
    use CustomControllerHelperTrait;

    public function getOfferId(): int
    {
        return $this->queryParameter('offerid')->int()->required();
    }
}
