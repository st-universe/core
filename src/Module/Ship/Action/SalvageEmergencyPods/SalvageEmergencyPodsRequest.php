<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use Stu\Lib\Request\CustomControllerHelperTrait;

/**
 * Request parameter accessor for starting emergency calls
 */
final class SalvageEmergencyPodsRequest implements SalvageEmergencyPodsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getShipId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    public function getTargetId(): int
    {
        return $this->queryParameter('target')->int()->required();
    }
}
