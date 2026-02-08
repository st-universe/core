<?php

namespace Stu\Module\Config\Model;

abstract class AbstractSettings implements SettingsInterface
{
    public function __construct(
        protected ?SettingsInterface $parent,
        protected SettingsCoreInterface $settingsCore,
        protected SettingsCacheInterface $settingsCache
    ) {}

    #[\Override]
    public function getPath(): string
    {
        return $this->settingsCore->getPath();
    }
}
