<?php

namespace Stu\Module\Config;

use Override;
use Stu\Module\Config\Model\CacheSettingsInterface;
use Stu\Module\Config\Model\DbSettingsInterface;
use Stu\Module\Config\Model\DebugSettingsInterface;
use Stu\Module\Config\Model\GameSettingsInterface;
use Stu\Module\Config\Model\ResetSettingsInterface;
use Stu\Module\Config\Model\SecuritySettingsInterface;
use Stu\Module\Config\Model\SettingsCacheInterface;

final class StuConfig implements StuConfigInterface
{
    public function __construct(
        private SettingsCacheInterface $settingsCache
    ) {}

    #[Override]
    public function getCacheSettings(): CacheSettingsInterface
    {
        return $this->settingsCache->getSettings(CacheSettingsInterface::class, null);
    }

    #[Override]
    public function getDbSettings(): DbSettingsInterface
    {
        return $this->settingsCache->getSettings(DbSettingsInterface::class, null);
    }

    #[Override]
    public function getDebugSettings(): DebugSettingsInterface
    {
        return $this->settingsCache->getSettings(DebugSettingsInterface::class, null);
    }

    #[Override]
    public function getGameSettings(): GameSettingsInterface
    {
        return $this->settingsCache->getSettings(GameSettingsInterface::class, null);
    }

    #[Override]
    public function getResetSettings(): ResetSettingsInterface
    {
        return $this->settingsCache->getSettings(ResetSettingsInterface::class, null);
    }

    #[Override]
    public function getSecuritySettings(): SecuritySettingsInterface
    {
        return $this->settingsCache->getSettings(SecuritySettingsInterface::class, null);
    }
}
