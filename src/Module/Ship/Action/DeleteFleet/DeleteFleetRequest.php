<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeleteFleet;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteFleetRequest implements DeleteFleetRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getShipId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
