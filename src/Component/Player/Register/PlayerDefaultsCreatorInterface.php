<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Stu\Orm\Entity\UserInterface;

interface PlayerDefaultsCreatorInterface
{
    public function createDefault(UserInterface $player): void;
}
