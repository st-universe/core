<?php

namespace Stu\Module\History\Lib;

use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Component\History\HistoryTypeEnum;

interface EntryCreatorInterface
{
    public function addEntry(
        string $text,
        int $sourceUserId,
        SpacecraftInterface|PlanetFieldHostInterface|AllianceInterface $target
    ): void;

    public function createEntry(
        HistoryTypeEnum $type,
        string $text,
        int $sourceUserId,
        int $targetUserId
    ): void;
}
