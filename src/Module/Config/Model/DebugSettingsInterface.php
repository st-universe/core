<?php

namespace Stu\Module\Config\Model;

interface DebugSettingsInterface
{
    public function isDebugMode(): bool;

    public function getLogfilePath(): string;

    public function getLoglevel(): int;

    public function getSqlLoggingSettings(): SqlLoggingSettingsInterface;
}
