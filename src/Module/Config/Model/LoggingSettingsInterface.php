<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

interface LoggingSettingsInterface
{
    public function getLogDirectory(): string;

    public function getGameRequestLoggingAdapter(): string;
}
