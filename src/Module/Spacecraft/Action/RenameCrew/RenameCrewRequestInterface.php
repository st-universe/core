<?php

namespace Stu\Module\Spacecraft\Action\RenameCrew;

interface RenameCrewRequestInterface
{
    public function getName(int $crewId): string;
}
