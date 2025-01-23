<?php

namespace Stu\Module\Config\Model;

interface SettingsCacheInterface
{
    /**
     * @template T
     * @param class-string<T> $configInterface Entry name or a class name.
     *
     * @return T
     */
    public function getSettings(string $configInterface, ?SettingsInterface $parent): mixed;
}
