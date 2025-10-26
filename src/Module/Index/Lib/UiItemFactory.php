<?php

declare(strict_types=1);

namespace Stu\Module\Index\Lib;

use Stu\Orm\Entity\Faction;

/**
 * Creates ui related items for the index area
 */
final class UiItemFactory implements UiItemFactoryInterface
{
    #[\Override]
    public function createFactionItem(
        Faction $faction,
        int $currentPlayerCount
    ): FactionItem {
        return new FactionItem(
            $faction,
            $currentPlayerCount
        );
    }
}
