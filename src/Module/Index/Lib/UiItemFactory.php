<?php

declare(strict_types=1);

namespace Stu\Module\Index\Lib;

use Stu\Orm\Entity\FactionInterface;

/**
 * Creates ui related items for the index area
 */
final class UiItemFactory implements UiItemFactoryInterface
{
    public function createFactionItem(
        FactionInterface $faction,
        int $currentPlayerCount
    ): FactionItem {
        return new FactionItem(
            $faction,
            $currentPlayerCount
        );
    }
}
