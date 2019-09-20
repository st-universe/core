<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use ColonyData;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonySurface implements ColonySurfaceInterface
{
    private $planetFieldRepository;

    private $buildingRepository;

    private $colony;

    private $buildingId;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingRepositoryInterface $buildingRepository,
        ColonyData $colony,
        ?int $buildingId = null
    ) {
        $this->colony = $colony;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingRepository = $buildingRepository;
        $this->buildingId = $buildingId;
    }

    public function getSurface(): array
    {
        $fields = $this->planetFieldRepository->getByColony($this->colony->getId());

        if ($fields === []) {
            $this->colony->updateColonySurface();

            $fields = $this->planetFieldRepository->getByColony($this->colony->getId());
        }

        if ($this->buildingId !== null) {
            $building = $this->buildingRepository->find($this->buildingId);

            array_walk(
                $fields,
                function (PlanetFieldInterface $field) use ($building): void {
                    if (
                        $field->getTerraformingId() === null &&
                        $building->getBuildableFields()->containsKey((int)$field->getFieldType())
                    ) {
                        $field->setBuildMode(true);
                    }
                }
            );
        }

        return $fields;
    }

    public function getSurfaceTileCssClass(): string
    {
        if ($this->colony->getPlanetType()->getIsMoon()) {
            return 'moonSurfaceTiles';
        }
        return 'planetSurfaceTiles';
    }

    public function getEpsBoxTitleString(): string
    {
        return sprintf(
            _('Energie: %d/%d (%d/Runde = %d)'),
            $this->colony->getEps(),
            $this->colony->getMaxEps(),
            $this->colony->getEpsProductionDisplay(),
            $this->colony->getEpsProductionForecast());
    }
}