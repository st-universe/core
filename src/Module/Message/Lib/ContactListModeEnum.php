<?php

namespace Stu\Module\Message\Lib;

final class ContactListModeEnum
{
    public const CONTACT_FRIEND = 1;
    public const CONTACT_ENEMY = 2;
    public const CONTACT_NEUTRAL = 3;

    public const AVAILABLE_MODES = [
        self::CONTACT_FRIEND,
        self::CONTACT_ENEMY,
        self::CONTACT_NEUTRAL,
    ];
}
