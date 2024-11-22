<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

/**
 * Request parameter accessor for starting emergency calls
 */
final class SalvageEmergencyPodsRequest implements SalvageEmergencyPodsRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getShipId(): int
    {
        return $this->parameter('id')->int()->required();
    }

    #[Override]
    public function getTargetId(): int
    {
        return $this->parameter('target')->int()->required();
    }
}
