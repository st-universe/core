<?php

namespace Stu\Module\Config\Model;

interface LoggingSettingsInterface
{
    public function getLogDirectory(): string;

    public function getGameRequestLoggingAdapter(): string;
}
