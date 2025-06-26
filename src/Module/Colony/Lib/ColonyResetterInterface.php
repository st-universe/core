<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\Colony;

interface ColonyResetterInterface
{
    public function reset(Colony $colony, bool $sendMessage = true): void;
}
