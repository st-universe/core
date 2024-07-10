<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;

interface SelectedTechFactoryInterface
{
    public function createSelectedTech(ResearchInterface $research, UserInterface $currentUser): SelectedTechInterface;
}
