<?php

declare(strict_types=1);

namespace Stu\Module\Index\Lib;

use Stu\Orm\Entity\Faction;

interface UiItemFactoryInterface
{
    public function createFactionItem(Faction $faction, int $currentPlayerCount): FactionItem;
}
