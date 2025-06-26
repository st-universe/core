<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

class ColonyEpsProductionPreviewWrapper
{
    private ?int $preview = null;

    public function __construct(private PlanetFieldRepositoryInterface $planetFieldRepository, private PlanetFieldHostInterface $host, private Building $building)
    {
    }

    public function getDisplay(): string
    {
        $preview = $this->getPreview();
        if ($preview > 0) {
            return sprintf('+%d', $preview);
        }

        return (string) $preview;
    }

    private function getPreview(): int
    {
        if ($this->preview === null) {
            $this->preview = $this->planetFieldRepository->getEnergyProductionByHost($this->host) + $this->building->getEpsProduction();
        }

        return $this->preview;
    }


    public function getCSS(): string
    {
        if ($this->getPreview() > 0) {
            return 'positive';
        }
        if ($this->getPreview() < 0) {
            return 'negative';
        }

        return '';
    }
}
