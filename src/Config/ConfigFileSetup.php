<?php

declare(strict_types=1);

namespace Stu\Config;

use Noodlehaus\Config;
use Noodlehaus\ConfigInterface;
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

    public static function load(): ConfigInterface
    {
        $config = new Config(array_map(
            fn (string $file): string => sprintf($file, __DIR__ . '/../../config/'),
            self::getConfigFileSetup()
        ));
        $extensions = getenv('STU_EXTENSIONS');
        if ($extensions !== false && $extensions !== '') {
            $config->set('extensions', json_decode($extensions, true, 512, JSON_THROW_ON_ERROR));
        }
        return $config;
    }
}
