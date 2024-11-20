<?php

namespace Stu\Module\Config\Model;

use Override;

final class DbSettings extends AbstractSettings implements DbSettingsInterface
{
    private const string SETTING_USE_SQLITE = 'useSqlite';
    private const string SETTING_SQLITE_DSN = 'sqliteDsn';
    private const string SETTING_DATABASE = 'database';
    private const string SETTING_PROXY_NAMESPACE = 'proxy_namespace';

    #[Override]
    public function useSqlite(): bool
    {
        return $this->settingsCore->getBooleanConfigValue(self::SETTING_USE_SQLITE, false);
    }

    #[Override]
    public function getSqliteDsn(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_SQLITE_DSN);
    }

    #[Override]
    public function getDatabase(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_DATABASE);
    }

    #[Override]
    public function getProxyNamespace(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_PROXY_NAMESPACE);
    }
}
