<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

class WarpCoreSystemData extends AbstractSystemData
{
    // warpdrive fields
    public int $split = 50;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    public function __construct(ShipSystemRepositoryInterface $shipSystemRepository)
    {
        $this->shipSystemRepository = $shipSystemRepository;
    }

    public function update(): void
    {
        // Überprüfe und begrenze den Wert zwischen 0 und 100
        $this->split = max(0, min(100, $this->split));

        $this->updateSystemData(
            ShipSystemTypeEnum::SYSTEM_WARPCORE,
            $this,
            $this->shipSystemRepository
        );
    }

    public function getWarpCoreSplit(): int
    {
        return $this->split;
    }

    public function setWarpCoreSplit(int $split): WarpCoreSystemData
    {
        // Überprüfe und begrenze den Wert zwischen 0 und 100
        $this->split = max(0, min(100, $split));
        return $this;
    }
}
