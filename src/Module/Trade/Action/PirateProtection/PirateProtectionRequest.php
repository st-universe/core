<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\PirateProtection;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class PirateProtectionRequest implements PirateProtectionRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPrestige(): int
    {
        return $this->queryParameter('prestige')->int()->defaultsTo(0);
    }
}
