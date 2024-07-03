<?php

namespace Stu\Module\Config\Model;

use Override;
final class DbSettings extends AbstractSettings implements DbSettingsInterface
{
    private const string CONFIG_PATH = 'db';

    private const string SETTING_USE_SQLITE = 'useSqlite';
    private const string SETTING_DATABASE = 'database';
    private const string SETTING_PROXY_NAMESPACE = 'proxy_namespace';

    #[Override]
    public function useSqlite(): bool
    {
        return $this->getBooleanConfigValue(self::SETTING_USE_SQLITE, false);
    }

    #[Override]
    public function getDatabase(): string
    {
        return $this->getStringConfigValue(self::SETTING_DATABASE);
    }

    #[Override]
    public function getProxyNamespace(): string
    {
        return $this->getStringConfigValue(self::SETTING_PROXY_NAMESPACE);
    }

    #[Override]
    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
