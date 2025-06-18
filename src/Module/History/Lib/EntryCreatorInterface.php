<?php

namespace Stu\Module\History\Lib;

use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface EntryCreatorInterface
{
    public function addEntry(
        string $text,
        int $sourceUserId,
        SpacecraftInterface|PlanetFieldHostInterface|AllianceInterface $target
    ): void;
}
