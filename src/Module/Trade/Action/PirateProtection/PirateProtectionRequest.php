<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\PirateProtection;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class PirateProtectionRequest implements PirateProtectionRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPrestige(): int
    {
        return $this->queryParameter('prestige')->int()->defaultsTo(0);
    }
}
