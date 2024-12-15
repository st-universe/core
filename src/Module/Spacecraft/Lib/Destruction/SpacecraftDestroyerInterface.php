<?php

namespace Stu\Module\Spacecraft\Lib\Destruction;

use Stu\Orm\Entity\UserInterface;

interface SpacecraftDestroyerInterface
{
    public function getUser(): UserInterface;

    public function getName(): string;
}
