<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;

interface ColonyResetterInterface
{
    public function reset(ColonyInterface $colony, bool $sendMessage = true): void;
}
