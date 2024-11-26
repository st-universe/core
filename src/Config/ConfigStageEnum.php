<?php

namespace Stu\Config;

enum ConfigStageEnum: string
{
    case PRODUCTION = 'production';
    case DEVELOPMENT = 'dev';
    case INTEGRATION_TEST = 'inttest';

    /** @return array<string> */
    public function getAdditionalConfigFiles(): array
    {
        return match ($this) {
            self::INTEGRATION_TEST => [
                '%s/config.intttest.dist.json',
                '?%s/config.intttest.json'
            ],
            default => []
        };
    }
}
