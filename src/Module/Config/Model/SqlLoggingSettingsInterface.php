<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

interface SqlLoggingSettingsInterface
{
    public function isActive(): bool;
}
