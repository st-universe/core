<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\UserInterface;

interface ColonizationCheckerInterface
{
    public function canColonize(UserInterface $user, ColonyInterface $colony): bool;
}
