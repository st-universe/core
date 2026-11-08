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
        if (!$location->getFieldType()->getPassable()) {
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
        $location = $ship->getLocation();
        if (!$location instanceof StarSystemMap || $location->getSystem() !== $target->getSystem()) {
            return;
        }
        $candidates = $this->systemNeighbors($location);
        usort($candidates, static fn (StarSystemMap $a, StarSystemMap $b): int => (abs($a->getX() - $target->getX()) + abs($a->getY() - $target->getY())) <=> (abs($b->getX() - $target->getX()) + abs($b->getY() - $target->getY())));
        foreach ($candidates as $candidate) {
            if ($this->isSafe($candidate)) {
                $this->flight->move($ship, $candidate);
                return;
            }
        }
    }

    public function roam(Ship $ship, BorgSphereConfiguration $configuration): void
    {
        $location = $ship->getLocation();
        $candidates = $location instanceof StarSystemMap ? $this->systemRoamingLocations($location) : $this->mapRoamingLocations($location, $configuration);
        $candidates = array_values(array_filter($candidates, fn (Location $field): bool => $this->isSafe($field)));
        if ($candidates !== []) {
            $this->flight->move($ship, $candidates[$this->random->array_rand($candidates)]);
        }
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
        return $field->getX() === 1 || $field->getY() === 1 || $field->getX() === $field->getSystem()->getMaxX() || $field->getY() === $field->getSystem()->getMaxY();
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
