<?php

namespace Stu\Module\Config\Model;

interface SqlLoggingSettingsInterface
{
    public function isActive(): bool;

    public function getLogDirectory(): string;
}
