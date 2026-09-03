<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\PirateProtection;

interface PirateProtectionRequestInterface
{
    public function getPrestige(): int;
}
