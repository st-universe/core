<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

interface ResetSettingsInterface
{
    public function getDelayInSeconds(): int;
}
