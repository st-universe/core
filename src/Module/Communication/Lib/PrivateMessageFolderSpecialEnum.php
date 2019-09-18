<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

class PrivateMessageFolderSpecialEnum
{

    public const DEFAULT_CATEGORIES = [
        PM_SPECIAL_MAIN => 'Persönlich',
        PM_SPECIAL_SHIP => 'Schiffe',
        PM_SPECIAL_COLONY => 'Kolonien',
        PM_SPECIAL_TRADE => 'Handel',
        PM_SPECIAL_PMOUT => 'Postausgang'
    ];
}