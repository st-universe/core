<?php

declare(strict_types=1);

namespace Stu\Lib\Colony;

use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\User;

interface PlanetFieldHostProviderInterface
{
    public function loadFieldViaRequestParameter(User $user, bool $checkForEntityLock = true): PlanetField;

    public function loadHostViaRequestParameters(User $user, bool $checkForEntityLock = true): PlanetFieldHostInterface;
}
