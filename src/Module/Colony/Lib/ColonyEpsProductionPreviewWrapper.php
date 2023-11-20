<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

class ColonyEpsProductionPreviewWrapper
{
    private PlanetFieldHostInterface $host;
    private BuildingInterface $building;
    private ?int $preview = null;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        PlanetFieldHostInterface $host,
        BuildingInterface $building
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->host = $host;
        $this->building = $building;
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
            $this->preview = $this->planetFieldRepository->getEnergyProductionByColony($this->host) + $this->building->getEpsProduction();
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
