<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\LssBlockade;

use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Entity\Location;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class LssBlockadeGridFactory
{
    public function __construct(
        private readonly MapRepositoryInterface $mapRepository,
        private readonly StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {}

    public function createLssBlockadeGrid(Location $observerLocation, AbstractVisualPanel $panel): LssBlockadeGrid
    {
        $boundaries = $panel->getBoundaries();

        $blockadeGrid = new LssBlockadeGrid(
            $boundaries->getMinX(),
            $boundaries->getMaxX(),
            $boundaries->getMinY(),
            $boundaries->getMaxY(),
            $observerLocation->getX(),
            $observerLocation->getY()
        );

        $blockedLocations = $this->getBlockedCoords($boundaries);

        array_walk(
            $blockedLocations,
            function (array $entry) use ($blockadeGrid): void {
                $blockadeGrid->setBlocked($entry['x'], $entry['y']);
            }
        );

        return $blockadeGrid;
    }

    /** @return array< array{x: int, y: int, effects: ?string}> */
    private function getBlockedCoords(PanelBoundaries $boundaries): array
    {
        $locations = $boundaries->isOnMap()
            ? $this->mapRepository->getLssBlockadeLocations($boundaries)
            : $this->starSystemMapRepository->getLssBlockadeLocations($boundaries);

        return array_filter(
            $locations,
            fn(array $entry): bool => $entry['effects'] !== null && str_contains($entry['effects'], FieldTypeEffectEnum::LSS_BLOCKADE->value)
        );
    }
}
