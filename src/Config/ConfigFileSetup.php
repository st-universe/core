<?php

namespace Stu\Config;

use RuntimeException;

class ConfigFileSetup
{
    /** @var array<string> */
    private const array DEFAULT_CONFIG_FILES = [
        '%s/config.dist.json',
        '?%s/config.json'
    ];

    /** @var null|array<string> */
    private static ?array $configFiles = null;

    public static function initConfigStage(ConfigStageEnum $stage): void
    {
        self::$configFiles = array_merge(self::DEFAULT_CONFIG_FILES, $stage->getAdditionalConfigFiles());
    }

    /** @return array<string> */
    public static function getConfigFileSetup(): array
    {
        if (self::$configFiles === null) {
            throw new RuntimeException('no config stage initialized!');
        }
        return self::$configFiles;
    }
}
