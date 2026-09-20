<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;
use Stu\Orm\Entity\User;

interface GameSessionInitializerInterface
{
    public function initialize(ModuleEnum $module): ?User;
}
