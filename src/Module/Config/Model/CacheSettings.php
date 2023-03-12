<?php

namespace Stu\Module\Config\Model;


final class CacheSettings extends AbstractSettings implements CacheSettingsInterface
{
    private const CONFIG_PATH = 'cache';

    private const SETTING_USE_REDIS = 'useRedis';
    private const SETTING_REDIS_SOCKET = 'redis_socket';
    private const SETTING_REDIS_HOST = 'redis_host';
    private const SETTING_REDIS_PORT = 'redis_port';

    public function useRedis(): bool
    {
        return $this->getBooleanConfigValue(self::SETTING_USE_REDIS, true);
    }

    public function getRedisSocket(): ?string
    {
        if ($this->exists(self::SETTING_REDIS_SOCKET)) {
            return $this->getStringConfigValue(self::SETTING_REDIS_SOCKET);
        }

        return null;
    }

    public function getRedisHost(): string
    {
        return $this->getStringConfigValue(self::SETTING_REDIS_HOST);
    }

    public function getRedisPort(): int
    {
        return $this->getIntegerConfigValue(self::SETTING_REDIS_PORT);
    }

    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
