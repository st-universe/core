<?php

namespace Stu\Module\Crew\Lib;

use CrewData;

interface CrewCreatorInterface
{
    public function create(int $userId): CrewData;
}