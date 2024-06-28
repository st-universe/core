<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;

class AstroLaboratorySystemData extends AbstractSystemData
{
    public ?int $astroStartTurn = null;

    function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY;
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
