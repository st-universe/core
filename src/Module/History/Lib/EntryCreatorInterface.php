<?php

namespace Stu\Module\History\Lib;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface EntryCreatorInterface
{
    public function addEntry(
        string $text,
        int $sourceUserId,
        ShipInterface|ColonyInterface|AllianceInterface $target
    ): void;
}
