<?php

namespace Stu\Module\Ship\Action\RenameCrew;

interface RenameCrewRequestInterface
{
    public function getName(int $crewId): string;
}
