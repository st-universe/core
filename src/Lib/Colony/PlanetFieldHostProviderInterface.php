<?php

declare(strict_types=1);

namespace Stu\Lib\Colony;

use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\UserInterface;

interface PlanetFieldHostProviderInterface
{
    public function loadFieldViaRequestParameter(UserInterface $user, bool $checkForEntityLock = true): PlanetFieldInterface;

    public function loadHostViaRequestParameters(UserInterface $user, bool $checkForEntityLock = true): PlanetFieldHostInterface;
}
