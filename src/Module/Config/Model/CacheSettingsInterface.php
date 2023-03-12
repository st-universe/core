<?php

namespace Stu\Module\Config\Model;

interface CacheSettingsInterface
{
    public function useRedis(): bool;

    public function getRedisSocket(): ?string;

    public function getRedisHost(): string;

    public function getRedisPort(): int;
}
