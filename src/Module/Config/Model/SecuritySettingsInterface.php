<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

interface SecuritySettingsInterface
{
    public function getMasterPassword(): ?string;
}
