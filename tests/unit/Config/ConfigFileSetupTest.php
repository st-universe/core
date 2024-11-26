<?php

declare(strict_types=1);

namespace Stu\Config;

use Stu\StuTestCase;

class ConfigFileSetupTest extends StuTestCase
{
    public function testGetConfigFileSetupExpectDefaultAndStageSet(): void
    {
        ConfigFileSetup::initConfigStage(ConfigStageEnum::INTEGRATION_TEST);

        $this->assertEquals([
            '%s/config.dist.json',
            '?%s/config.json',
            '%s/config.intttest.dist.json',
            '?%s/config.intttest.json'
        ], ConfigFileSetup::getConfigFileSetup());
    }
}
