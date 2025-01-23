<?php

namespace Stu\Module\Config\Model;

interface PirateSettingsInterface
{
    public function isPirateTickActive(): bool;

    public function getPirateLogfilePath(): string;
}
