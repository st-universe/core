<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

interface MapSettingsInterface
{
    public function getEncryptionKey(): ?string;
}
