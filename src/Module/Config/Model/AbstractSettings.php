<?php

namespace Stu\Module\Config\Model;

use Noodlehaus\ConfigInterface;
use Stu\Module\Config\StuConfigException;

abstract class AbstractSettings implements SettingsInterface
{
    public function __construct(private ?string $parentPath, private ConfigInterface $config)
    {
    }

    protected function getPath(): string
    {
        if ($this->parentPath === null) {
            return $this->getConfigPath();
        }
        return sprintf('%s.%s', $this->parentPath, $this->getConfigPath());
    }

    protected function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    protected function getIntegerConfigValue(string $setting, ?int $default = null): int
    {
        $path = $this->getSettingPath($setting);
        $value = $this->getConfigValue($path, $default);

        if (!is_numeric($value)) {
            throw new StuConfigException(sprintf('The value "%s" with path "%s" is no valid integer.', $value, $path));
        }

        return (int)$value;
    }

    protected function getStringConfigValue(string $setting, ?string $default = null): string
    {
        $path = $this->getSettingPath($setting);
        $value = $this->getConfigValue($path, $default);

        if (!is_string($value)) {
            throw new StuConfigException(sprintf('The value "%s" with path "%s" is no valid string.', $value, $path));
        }

        return $value;
    }

    protected function getBooleanConfigValue(string $setting, ?bool $default = null): bool
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
    protected function getArrayConfigValue(string $setting, ?array $default = null): array
    {
        $path = $this->getSettingPath($setting);

        return $this->getConfigArray($path, $default);
    }

    protected function exists(string $setting): bool
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
