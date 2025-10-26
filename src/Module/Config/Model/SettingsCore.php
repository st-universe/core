<?php

namespace Stu\Module\Config\Model;

use Noodlehaus\ConfigInterface;
use Stu\Module\Config\StuConfigException;

class SettingsCore implements SettingsCoreInterface
{
    public function __construct(
        private ?SettingsInterface $parentSetting,
        private string $configPath,
        private ConfigInterface $config
    ) {
    }

    #[\Override]
    public function getPath(): string
    {
        if ($this->parentSetting === null) {
            return $this->configPath;
        }
        return sprintf('%s.%s', $this->parentSetting->getPath(), $this->configPath);
    }

    #[\Override]
    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    #[\Override]
    public function getIntegerConfigValue(string $setting, ?int $default = null): int
    {
        $path = $this->getSettingPath($setting);
        $value = $this->getConfigValue($path, $default);

        if (!is_numeric($value)) {
            throw new StuConfigException(sprintf('The value "%s" with path "%s" is no valid integer.', $value, $path));
        }

        return (int)$value;
    }

    #[\Override]
    public function getStringConfigValue(string $setting, ?string $default = null): string
    {
        $path = $this->getSettingPath($setting);
        $value = $this->getConfigValue($path, $default);

        if (!is_string($value)) {
            throw new StuConfigException(sprintf('The value "%s" with path "%s" is no valid string.', $value, $path));
        }

        return $value;
    }

    #[\Override]
    public function getBooleanConfigValue(string $setting, ?bool $default = null): bool
    {
        $path = $this->getSettingPath($setting);
        $value = $this->getConfigValue($path, $default);

        if (!is_bool($value)) {
            throw new StuConfigException(sprintf('The value "%s" with path "%s" is no valid boolean.', $value, $path));
        }

        return $value;
    }

    /**
     * @param string[]|null $default
     *
     * @return array<string>
     */
    #[\Override]
    public function getArrayConfigValue(string $setting, ?array $default = null): array
    {
        $path = $this->getSettingPath($setting);

        return $this->getConfigArray($path, $default);
    }

    #[\Override]
    public function exists(string $setting): bool
    {
        $path = $this->getSettingPath($setting);
        $value = $this->getConfig()->get($path);

        return $value !== null;
    }

    /**
     * @return int|string|bool
     */
    private function getConfigValue(string $path, int|null|string|bool $default = null)
    {
        $value = $this->getConfig()->get($path);

        if ($value !== null) {
            return $value;
        }

        //throw exception if setting mandatory
        if ($default === null) {
            throw new StuConfigException(sprintf('There is no corresponding config setting on path "%s"', $path));
        }

        return $default;
    }

    /**
     * @param string[]|null $default
     *
     * @return array<string>
     */
    private function getConfigArray(string $path, ?array $default = null): array
    {
        $value = $this->getConfig()->get($path);

        if ($value !== null) {
            if (!is_array($value)) {
                throw new StuConfigException(sprintf('The value "%s" with path "%s" is no valid array.', $value, $path));
            }

            return $value;
        }

        //throw exception if setting mandatory
        if ($default === null) {
            throw new StuConfigException(sprintf('There is no corresponding config setting on path "%s"', $path));
        }

        return $default;
    }

    private function getSettingPath(string $setting): string
    {
        return sprintf('%s.%s', $this->getPath(), $setting);
    }
}
