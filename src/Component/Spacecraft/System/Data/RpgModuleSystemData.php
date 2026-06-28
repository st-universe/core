<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

class RpgModuleSystemData extends AbstractSystemData
{
    private const string INVISIBLE_KEY = 'invisible';
    public const string ACTIVE_INVISIBILITY_CONFIG_FRAGMENT = '"' . self::INVISIBLE_KEY . '":true';

    public bool $invisible = false;

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::RPG_MODULE;
    }

    public function isInvisible(): bool
    {
        return $this->invisible;
    }

    public function setInvisible(bool $invisible): RpgModuleSystemData
    {
        $this->invisible = $invisible;
        return $this;
    }

    public static function isInvisibleData(?string $data): bool
    {
        if ($data === null || trim($data) === '') {
            return false;
        }

        $config = json_decode($data, true);

        return is_array($config) && ($config[self::INVISIBLE_KEY] ?? false) === true;
    }

    public static function getActiveInvisibilityConfigSearchValue(): string
    {
        return sprintf('%%%s%%', self::ACTIVE_INVISIBILITY_CONFIG_FRAGMENT);
    }
}
