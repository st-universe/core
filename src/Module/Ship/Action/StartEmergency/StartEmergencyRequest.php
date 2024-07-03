<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartEmergency;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

/**
 * Request parameter accessor for starting emergency calls
 */
final class StartEmergencyRequest implements StartEmergencyRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getEmergencyText(): string
    {
        return $this->tidyString(
            $this->queryParameter('text')->string()->defaultsToIfEmpty('')
        );
    }

    #[Override]
    public function getShipId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }
}
