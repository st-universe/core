<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StopEmergency;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

/**
 * Request parameter accessor for stopping emergency calls
 */
final class StopEmergencyRequest implements StopEmergencyRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getShipId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
