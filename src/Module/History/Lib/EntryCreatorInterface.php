<?php

namespace Stu\Module\History\Lib;

use Stu\Component\History\HistoryTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;

interface EntryCreatorInterface
{
    public function addEntry(
        string $text,
        int $sourceUserId,
        Spacecraft|Colony|Alliance $target
    ): void;

    public function createEntry(
        HistoryTypeEnum $type,
        string $text,
        int $sourceUserId,
        int $targetUserId,
        ?Location $location = null
    ): void;
}
