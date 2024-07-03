<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\PriorizeFleet;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class PriorizeFleetRequest implements PriorizeFleetRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getFleetId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
