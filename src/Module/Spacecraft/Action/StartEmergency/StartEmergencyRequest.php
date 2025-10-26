<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StartEmergency;

use Stu\Lib\Request\CustomControllerHelperTrait;

/**
 * Request parameter accessor for starting emergency calls
 */
final class StartEmergencyRequest implements StartEmergencyRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getEmergencyText(): string
    {
        return $this->tidyString(
            $this->parameter('text')->string()->defaultsToIfEmpty('')
        );
    }

    #[\Override]
    public function getShipId(): int
    {
        return $this->parameter('id')->int()->required();
    }
}
