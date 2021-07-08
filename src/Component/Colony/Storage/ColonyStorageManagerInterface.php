<?php

namespace Stu\Component\Colony\Storage;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;

interface ColonyStorageManagerInterface
{
    public function lowerStorage(ColonyInterface $colony, CommodityInterface $commodity, int $amount): void;

    public function upperStorage(ColonyInterface $colony, CommodityInterface $commodity, int $amount, bool $flush = true): void;

    public function flush(): void;
}
