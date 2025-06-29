<?php

namespace Stu\Module\Config\Model;

interface SecuritySettingsInterface
{
    public function getMasterPassword(): ?string;
}
