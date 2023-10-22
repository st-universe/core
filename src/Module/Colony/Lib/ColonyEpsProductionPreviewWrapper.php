<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

class ColonyEpsProductionPreviewWrapper
{
    private ColonyInterface $colony;

    private ?int $buildingId = null;

    /** @var array<int, ColonyEpsProductionPreviewWrapper> */
    private array $wrappers = [];

    /** @var array<int, int> */
    private array $production = [];

    private BuildingRepositoryInterface $buildingRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingRepositoryInterface $buildingRepository,
        ColonyInterface $colony
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingRepository = $buildingRepository;
        $this->colony = $colony;
    }

    /**
     * @param int|string $buildingId
     */
    public function __get($buildingId): ColonyEpsProductionPreviewWrapper
    {
        $this->buildingId = (int) $buildingId;
        if (isset($this->wrappers[$this->buildingId])) {
            return $this->wrappers[$this->buildingId];
        }
        $this->wrappers[$this->buildingId] = $this;
        return $this->wrappers[$this->buildingId];
    }

    public function getBuildingId(): ?int
    {
        return $this->buildingId;
    }

    private function getPreview(): int
    {
        $buildingId = (int) $this->getBuildingId();

        if (!isset($this->production[$buildingId])) {
            $building = $this->buildingRepository->find($buildingId);
            if ($building === null) {
                return 0;
            }

            $this->production[$buildingId] = $this->planetFieldRepository->getEnergyProductionByColony($this->colony->getId()) + $building->getEpsProduction();
        }
        return $this->production[$buildingId];
    }

    public function getDisplay(): string
    {
        if ($this->getPreview() > 0) {
            return sprintf('+%d', $this->getPreview());
        }
        return (string) $this->getPreview();
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
