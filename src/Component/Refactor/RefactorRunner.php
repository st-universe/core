<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperProjectileWeapon;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\MapRegionInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class RefactorRunner
{
    public function __construct(
        private AstroEntryRepositoryInterface $astroEntryRepository,
        private LocationRepositoryInterface $locationRepository,
        private MapRepositoryInterface $mapRepository,
        private StarSystemMapRepositoryInterface $starSystemMapRepository,
    ) {
    }

    public function refactor(): void
    {
        foreach ($this->astroEntryRepository->findAll() as $astroEntry) {

            if ($astroEntry->getFieldIds() === '') {
                continue;
            }

            $fieldIds = new ArrayCollection(unserialize($astroEntry->getFieldIds()));
            if ($fieldIds->isEmpty()) {
                continue;
            }

            $locations = $fieldIds->map(fn (int $id) => $this->locationRepository->find($id));

            $lost = false;
            $isMap = false;
            $isSystemMap = false;

            /** @var LocationInterface|null $location */
            foreach ($locations as $location) {
                if ($location === null) {
                    $lost = true;
                    break;
                }

                if (
                    $location instanceof MapInterface
                    && $location->getMapRegion() !== $astroEntry->getRegion()
                ) {
                    $lost = true;
                    break;
                }

                if (
                    $location instanceof StarSystemMapInterface
                    && $location->getSystem() !== $astroEntry->getSystem()
                ) {
                    $lost = true;
                    break;
                }

                $isMap = $isMap || $location->isMap();
                $isSystemMap = $isSystemMap || !$location->isMap();
            }

            if ($lost || ($isMap && $isSystemMap)) {
                $this->obtainMeasurementFields(
                    $astroEntry->getSystem(),
                    $astroEntry->getRegion(),
                    $astroEntry
                );
            }
        }
    }

    private function obtainMeasurementFields(
        ?StarSystemInterface $system,
        ?MapRegionInterface $mapRegion,
        AstronomicalEntryInterface $entry
    ): void {
        if ($system !== null) {
            $this->obtainMeasurementFieldsForSystem($system, $entry);
        }
        if ($mapRegion !== null) {
            $this->obtainMeasurementFieldsForRegion($mapRegion, $entry);
        }

        $this->astroEntryRepository->save($entry);
    }

    private function obtainMeasurementFieldsForSystem(StarSystemInterface $system, AstronomicalEntryInterface $entry): void
    {
        $idArray = $this->starSystemMapRepository->getRandomSystemMapIdsForAstroMeasurement($system->getId());

        $entry->setFieldIds(serialize($idArray));
    }

    private function obtainMeasurementFieldsForRegion(MapRegionInterface $mapRegion, AstronomicalEntryInterface $entry): void
    {
        $mapIds = $this->mapRepository->getRandomMapIdsForAstroMeasurement($mapRegion->getId(), 25);

        $entry->setFieldIds(serialize($mapIds));
    }
}
