<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Creation;

interface SpacecraftCorrectorInterface
{
    public function correctAllSpacecrafts(): void;
}
