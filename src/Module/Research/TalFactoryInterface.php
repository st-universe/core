<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Orm\Entity\ResearchInterface;
use UserData;

interface TalFactoryInterface
{
    public function createTalSelectedTech(ResearchInterface $research, UserData $currentUser): TalSelectedTechInterface;
}