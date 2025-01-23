<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTakeOffer;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowTakeOfferRequest implements ShowTakeOfferRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getOfferId(): int
    {
        return $this->parameter('offerid')->int()->required();
    }
}
