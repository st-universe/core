<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTakeOffer;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowTakeOfferRequest implements ShowTakeOfferRequestInterface
{
    use CustomControllerHelperTrait;

    public function getOfferId(): int
    {
        return $this->queryParameter('offerid')->int()->required();
    }
}