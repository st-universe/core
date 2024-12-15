<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

enum SpacecraftTypeEnum: string
{
    case SHIP = 'SHIP';
    case STATION = 'STATION';

    public function getDescription(): string
    {
        return match ($this) {
            self::SHIP => 'Schiff',
            self::STATION => 'Station'
        };
    }

    public function getMessageFolderType(): PrivateMessageFolderTypeEnum
    {
        return match ($this) {
            self::SHIP => PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            self::STATION => PrivateMessageFolderTypeEnum::SPECIAL_STATION
        };
    }

    public function getModuleView(): ModuleViewEnum
    {
        return match ($this) {
            self::SHIP => ModuleViewEnum::SHIP,
            self::STATION => ModuleViewEnum::STATION
        };
    }

    public function getViewIdentifier(): string
    {
        return match ($this) {
            self::SHIP => ShowSpacecraft::VIEW_IDENTIFIER,
            self::STATION => ShowSpacecraft::VIEW_IDENTIFIER
        };
    }
}
