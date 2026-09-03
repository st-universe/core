<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\RenameCrew;

interface RenameCrewRequestInterface
{
    public function getName(int $crewId): string;
}
