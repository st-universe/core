<?php

namespace Stu\Module\Config\Model;

use Noodlehaus\ConfigInterface;

interface SettingsCoreInterface
{
    public function getPath(): string;

    public function getConfig(): ConfigInterface;

    public function exists(string $setting): bool;

    public function getIntegerConfigValue(string $setting, ?int $default = null): int;

    public function getStringConfigValue(string $setting, ?string $default = null): string;

    public function getBooleanConfigValue(string $setting, ?bool $default = null): bool;

    /**
     * @param string[]|null $default
     *
     * @return array<mixed>
     */
    public function getArrayConfigValue(string $setting, ?array $default = null): array;
}
