<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use Stu\Orm\Entity\UserInterface;

interface AttackerInterface
{
    public function getName(): string;

    public function getUser(): UserInterface;
}
