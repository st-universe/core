<?php

namespace Stu\Module\Config\Model;

use Override;

final class CacheSettings extends AbstractSettings implements CacheSettingsInterface
{
    private const string CONFIG_PATH = 'cache';

    private const string SETTING_USE_REDIS = 'useRedis';
    private const string SETTING_REDIS_SOCKET = 'redis_socket';
    private const string SETTING_REDIS_HOST = 'redis_host';
    private const string SETTING_REDIS_PORT = 'redis_port';

    #[Override]
    public function useRedis(): bool
    {
        return $this->getBooleanConfigValue(self::SETTING_USE_REDIS, true);
    }

    #[Override]
    public function getRedisSocket(): ?string
    {
        if ($this->exists(self::SETTING_REDIS_SOCKET)) {
            return $this->getStringConfigValue(self::SETTING_REDIS_SOCKET);
        }

        return null;
    }

    #[Override]
    public function getRedisHost(): string
    {
        return $this->getStringConfigValue(self::SETTING_REDIS_HOST);
    }

    #[Override]
    public function getRedisPort(): int
    {
        return $this->getIntegerConfigValue(self::SETTING_REDIS_PORT);
    }

    #[Override]
    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
