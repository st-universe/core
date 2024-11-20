<?php

namespace Stu\Module\Config\Model;

interface EmailSettingsInterface
{
    public function getTransportDsn(): string;
    public function getSenderAddress(): string;
}
