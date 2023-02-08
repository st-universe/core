<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;

class ColonyEpsProductionPreviewWrapper
{
    /** @var ColonyInterface */
    private $colony;

    /** @var null|int */
    private $buildingId = null;

    /** @var array<int, ColonyEpsProductionPreviewWrapper> */
    private $wrappers = [];

    /** @var array<int, int> */
    private $production = [];

    private BuildingRepositoryInterface $buildingRepository;

    public function __construct(
        BuildingRepositoryInterface $buildingRepository,
        ColonyInterface $colony
    ) {
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

            $this->production[$buildingId] = $this->colony->getEpsProduction() + $building->getEpsProduction();
        }
        return $this->production[$buildingId];
    }

    public function getDisplay(): string
    {
        if ($this->getPreview()) {
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
