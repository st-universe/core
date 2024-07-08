<?php

namespace Stu\Module\Config\Model;

use Override;

final class CacheSettings extends AbstractSettings implements CacheSettingsInterface
{
    private const string SETTING_USE_REDIS = 'useRedis';
    private const string SETTING_REDIS_SOCKET = 'redis_socket';
    private const string SETTING_REDIS_HOST = 'redis_host';
    private const string SETTING_REDIS_PORT = 'redis_port';

    #[Override]
    public function useRedis(): bool
    {
        return $this->settingsCore->getBooleanConfigValue(self::SETTING_USE_REDIS, true);
    }

    #[Override]
    public function getRedisSocket(): ?string
    {
        if ($this->settingsCore->exists(self::SETTING_REDIS_SOCKET)) {
            return $this->settingsCore->getStringConfigValue(self::SETTING_REDIS_SOCKET);
        }

        return null;
    }

    #[Override]
    public function getRedisHost(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_REDIS_HOST);
    }

    #[Override]
    public function getRedisPort(): int
    {
        return $this->settingsCore->getIntegerConfigValue(self::SETTING_REDIS_PORT);
    }
}
