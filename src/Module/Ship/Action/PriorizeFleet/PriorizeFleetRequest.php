<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\PriorizeFleet;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class PriorizeFleetRequest implements PriorizeFleetRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getFleetId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
