<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\User;

interface SelectedTechFactoryInterface
{
    public function createSelectedTech(Research $research, User $currentUser): SelectedTechInterface;
}
