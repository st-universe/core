<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class BorgSphereNavigation
{
    public function __construct(
        private MapRepositoryInterface $maps,
        private StarSystemMapRepositoryInterface $systemMaps,
        private SpacecraftWrapperFactoryInterface $wrappers,
        private BorgSphereFlight $flight,
        private StuRandom $random
    ) {}

    public function isSafe(Location $location): bool
    {
        $fieldType = $location->getFieldType();
        if (!$fieldType->getPassable() || $fieldType->getSpecialDamage() > 0) {
            return false;
        }
        foreach ($location->getSpacecrafts() as $ship) {
            if ($ship->hasComputer() && $this->wrappers->wrapSpacecraft($ship)->getAlertState() === SpacecraftAlertStateEnum::ALERT_RED) {
                return false;
            }
        }
        return true;
    }

    public static function isWithinRadius(int $dx, int $dy, int $radius): bool
    {
        return $radius >= 0 && $dx * $dx + $dy * $dy <= $radius * $radius;
    }

    public function spawnLocation(BorgSphereConfiguration $configuration): ?StarSystemMap
    {
        $target = $configuration->getColony()->getStarsystemMap();
        $candidates = array_filter($this->systemMaps->getBySystemOrdered($target->getSystemId()), fn (StarSystemMap $field): bool => $field !== $target && $this->isSafe($field));
        usort($candidates, static fn (StarSystemMap $a, StarSystemMap $b): int => (abs($b->getX() - $target->getX()) + abs($b->getY() - $target->getY())) <=> (abs($a->getX() - $target->getX()) + abs($a->getY() - $target->getY())));
        return $candidates[0] ?? null;
    }

    public function approach(Ship $ship, BorgSphereConfiguration $configuration): void
    {
        $target = $configuration->getColony()->getStarsystemMap();
        $this->travel($ship, $configuration, fn (Location $location, array $visited): ?StarSystemMap => $this->approachDestination($location, $target, $visited));
    }

    /** @param list<Location> $visited */
    private function approachDestination(Location $location, StarSystemMap $target, array $visited): ?StarSystemMap
    {
        if ($location === $target || !$location instanceof StarSystemMap || $location->getSystem() !== $target->getSystem()) {
            return null;
        }
        $candidates = $this->systemNeighbors($location);
        usort($candidates, static fn (StarSystemMap $a, StarSystemMap $b): int => (abs($a->getX() - $target->getX()) + abs($a->getY() - $target->getY())) <=> (abs($b->getX() - $target->getX()) + abs($b->getY() - $target->getY())));
        return $this->safeLocations($candidates, $visited)[0] ?? null;
    }

    public function roam(Ship $ship, BorgSphereConfiguration $configuration): void
    {
        $this->travel($ship, $configuration, fn (Map|StarSystemMap $location, array $visited): Map|StarSystemMap|null => $this->roamingDestination($location, $configuration, $visited));
    }

    /** @param list<Location> $visited */
    private function roamingDestination(Map|StarSystemMap $location, BorgSphereConfiguration $configuration, array $visited): Map|StarSystemMap|null
    {
        $candidates = $location instanceof StarSystemMap ? $this->systemRoamingLocations($location) : $this->mapRoamingLocations($location, $configuration);
        $candidates = $this->safeLocations($candidates, $visited);
        return $candidates === [] ? null : $candidates[$this->random->array_rand($candidates)];
    }

    /** @param callable(Map|StarSystemMap, list<Location>): (Map|StarSystemMap|null) $destination */
    private function travel(Ship $ship, BorgSphereConfiguration $configuration, callable $destination): void
    {
        $visited = [$ship->getLocation()];
        for ($remaining = $this->movementBudget($configuration); $remaining > 0; $remaining--) {
            if ($ship->getCondition()->isDisabled() || $ship->getCondition()->isDestroyed()) {
                return;
            }
            $location = $ship->getLocation();
            $next = $destination($location, $visited);
            if ($next === null) {
                return;
            }
            $this->flight->move($ship, $next);
            if ($ship->getLocation() === $location) {
                return;
            }
            $visited[] = $ship->getLocation();
        }
    }

    private function movementBudget(BorgSphereConfiguration $configuration): int
    {
        $maximum = max(1, $configuration->getSetting('movement_max_fields'));
        $minimum = max(1, min($maximum, $configuration->getSetting('movement_min_fields')));
        return $this->random->rand($minimum, $maximum);
    }

    /**
     * @template T of Location
     * @param list<T> $candidates
     * @param list<Location> $visited
     * @return list<T>
     */
    private function safeLocations(array $candidates, array $visited): array
    {
        return array_values(array_filter($candidates, fn (Location $field): bool => !in_array($field, $visited, true) && $this->isSafe($field)));
    }

    /** @return list<StarSystemMap> */
    private function systemNeighbors(StarSystemMap $location): array
    {
        $neighbors = [];
        foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
            $field = $this->systemMaps->getByCoordinates($location->getSystemId(), $location->getX() + $dx, $location->getY() + $dy);
            if ($field !== null) {
                $neighbors[] = $field;
            }
        }
        return $neighbors;
    }

    /** @return list<Map|StarSystemMap> */
    private function systemRoamingLocations(StarSystemMap $location): array
    {
        $neighbors = $this->systemNeighbors($location);
        $map = $location->getSystem()->getMap();
        if ($map !== null && $this->isEdge($location)) {
            $neighbors[] = $map;
        }
        return $neighbors;
    }

    /** @return list<Map|StarSystemMap> */
    private function mapRoamingLocations(Map $location, BorgSphereConfiguration $configuration): array
    {
        $anchor = $configuration->getColony()->getSystem()->getMap();
        if ($anchor === null) {
            return [];
        }
        $neighbors = [];
        $radius = $configuration->getSetting('roam_radius');
        foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
            $x = $location->getX() + $dx;
            $y = $location->getY() + $dy;
            if (!self::isWithinRadius($x - $anchor->getX(), $y - $anchor->getY(), $radius)) {
                continue;
            }
            $field = $this->maps->getByCoordinates($anchor->getLayer(), $x, $y);
            if ($field !== null) {
                $neighbors[] = $field;
            }
        }
        $entry = $this->systemEntry($location);
        if ($entry !== null) {
            $neighbors[] = $entry;
        }
        return $neighbors;
    }

    private function isEdge(StarSystemMap $field): bool
    {
        return in_array($field->getX(), [1, $field->getSystem()->getMaxX()], true)
            || in_array($field->getY(), [1, $field->getSystem()->getMaxY()], true);
    }

    private function systemEntry(Map $location): ?StarSystemMap
    {
        $system = $location->getSystem();
        if ($system === null) {
            return null;
        }
        foreach ($this->systemMaps->getBySystemOrdered($system->getId()) as $field) {
            if ($field->getColony() === null && $this->isEdge($field) && $this->isSafe($field)) {
                return $field;
            }
        }
        return null;
    }

}
