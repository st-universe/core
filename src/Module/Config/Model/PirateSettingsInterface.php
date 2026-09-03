<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

interface PirateSettingsInterface
{
    public function isPirateTickActive(): bool;
}
