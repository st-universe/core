<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\PirateProtection;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class PirateProtectionRequest implements PirateProtectionRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getPrestige(): int
    {
        return $this->parameter('prestige')->int()->defaultsTo(0);
    }
}
