<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

class AstroLaboratorySystemData extends AbstractSystemData
{
    public ?int $astroStartTurn = null;

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::ASTRO_LABORATORY;
    }

    public function getAstroStartTurn(): ?int
    {
        return $this->astroStartTurn;
    }

    public function setAstroStartTurn(?int $turn): AstroLaboratorySystemData
    {
        $this->astroStartTurn = $turn;
        return $this;
    }
}
