<?php

namespace Stu\Module\Ship\Lib\Destruction;

use Stu\Orm\Entity\UserInterface;

interface ShipDestroyerInterface
{
    public function getUser(): UserInterface;

    public function getName(): string;
}
