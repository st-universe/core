<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelOffer;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CancelOfferRequest implements CancelOfferRequestInterface
{
    use CustomControllerHelperTrait;

    public function getRelationId(): int
    {
        return $this->queryParameter('al')->int()->required();
    }
}