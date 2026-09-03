<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

interface DebugSettingsInterface
{
    public function isDebugMode(): bool;

    public function getLoglevel(): int;

    public function getLoggingSettings(): LoggingSettingsInterface;

    public function getSqlLoggingSettings(): SqlLoggingSettingsInterface;
}
