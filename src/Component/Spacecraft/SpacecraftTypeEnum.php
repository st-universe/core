<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

use RuntimeException;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

enum SpacecraftTypeEnum: string
{
    case SHIP = 'SHIP';
    case STATION = 'STATION';
    case THOLIAN_WEB = 'THOLIAN_WEB';

    public function getDescription(): string
    {
        return match ($this) {
            self::SHIP => 'Schiff',
            self::STATION => 'Station',
            self::THOLIAN_WEB => 'Energienetz'
        };
    }

    public function getMessageFolderType(): PrivateMessageFolderTypeEnum
    {
        return match ($this) {
            self::SHIP => PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            self::STATION => PrivateMessageFolderTypeEnum::SPECIAL_STATION,
            default => throw new RuntimeException(sprintf('unsupported operation for %s', $this->name))
        };
    }

    public function getModuleView(): ?ModuleEnum
    {
        return match ($this) {
            self::SHIP => ModuleEnum::SHIP,
            self::STATION => ModuleEnum::STATION,
            default => null
        };
    }

    public function getViewIdentifier(): string
    {
        return match ($this) {
            self::SHIP => ShowSpacecraft::VIEW_IDENTIFIER,
            self::STATION => ShowSpacecraft::VIEW_IDENTIFIER,
            default => throw new RuntimeException(sprintf('unsupported operation for %s', $this->name))
        };
    }

    public function isTransferPossible(): bool
    {
        return match ($this) {
            self::THOLIAN_WEB => false,
            default => true
        };
    }
}
