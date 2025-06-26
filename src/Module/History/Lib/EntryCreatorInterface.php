<?php

namespace Stu\Module\History\Lib;

use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Spacecraft;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Location;

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
