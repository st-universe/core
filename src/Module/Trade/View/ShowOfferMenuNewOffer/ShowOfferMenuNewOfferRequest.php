<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferMenuNewOffer;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowOfferMenuNewOfferRequest implements ShowOfferMenuNewOfferRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getStorageId(): int
    {
        return $this->parameter('storid')->int()->required();
    }
}
