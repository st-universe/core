<?php

namespace Stu\Module\Config\Model;


final class DbSettings extends AbstractSettings implements DbSettingsInterface
{
    private const CONFIG_PATH = 'db';

    private const SETTING_USE_SQLITE = 'useSqlite';
    private const SETTING_DATABASE = 'database';
    private const SETTING_PROXY_NAMESPACE = 'proxy_namespace';

    public function useSqlite(): bool
    {
        return $this->getBooleanConfigValue(self::SETTING_USE_SQLITE, false);
    }

    public function getDatabase(): string
    {
        return $this->getStringConfigValue(self::SETTING_DATABASE);
    }

    public function getProxyNamespace(): string
    {
        return $this->getStringConfigValue(self::SETTING_PROXY_NAMESPACE);
    }

    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
