<?php

namespace Stu\Module\History\Lib;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\LocationInterface;

interface EntryCreatorInterface
{
    public function addEntry(
        string $text,
        int $sourceUserId,
        SpacecraftInterface|ColonyInterface|AllianceInterface $target
    ): void;

    public function createEntry(
        HistoryTypeEnum $type,
        string $text,
        int $sourceUserId,
        int $targetUserId,
        ?LocationInterface $location = null
    ): void;
}
