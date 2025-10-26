<?php

namespace Stu\Module\Config\Model;


final class EmailSettings extends AbstractSettings implements EmailSettingsInterface
{
    private const string SETTING_TRANSPORT_DSN = 'transportDsn';
    private const string SETTING_SENDER_ADDRESS = 'senderAddress';

    #[\Override]
    public function getTransportDsn(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_TRANSPORT_DSN);
    }

    #[\Override]
    public function getSenderAddress(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_SENDER_ADDRESS);
    }
}
