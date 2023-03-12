<?php

declare(strict_types=1);

namespace Stu\Module\Index\Lib;


use Stu\Orm\Entity\FactionInterface;

interface UiItemFactoryInterface
{
    public function createFactionItem(FactionInterface $faction, int $currentPlayerCount): FactionItem;
}