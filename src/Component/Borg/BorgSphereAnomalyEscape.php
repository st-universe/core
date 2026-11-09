<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystemMap;

final class BorgSphereAnomalyEscape
{
    public static function hasAnomaly(Location $location): bool
    {
        return $location->getAnomalies()->exists(static fn (int $key, Anomaly $anomaly): bool => $anomaly->isActive());
    }

    public static function isTraversable(Location $location, SpacecraftWrapperFactoryInterface $wrappers): bool
    {
        $fieldType = $location->getFieldType();
        if (!$fieldType->getPassable() || $fieldType->getSpecialDamage() > 0) {
            return false;
        }
        foreach ($location->getSpacecrafts() as $ship) {
            if ($ship->hasComputer() && $wrappers->wrapSpacecraft($ship)->getAlertState() === SpacecraftAlertStateEnum::ALERT_RED) {
                return false;
            }
        }
        return true;
    }

    public static function isWithinRadius(Location $location, BorgSphereConfiguration $configuration): bool
    {
        if (!$location instanceof Map) {
            return true;
        }
        $anchor = $configuration->getColony()->getSystem()->getMap();
        return $anchor !== null && BorgSphereNavigation::isWithinRadius($location->getX() - $anchor->getX(), $location->getY() - $anchor->getY(), $configuration->getSetting('roam_radius'));
    }

    /**
     * @param callable(Map|StarSystemMap): list<Map|StarSystemMap> $neighbors
     * @param callable(Location): bool $traversable
     * @return list<Map|StarSystemMap>
     */
    public static function findPath(Map|StarSystemMap $start, int $budget, callable $neighbors, callable $traversable): array
    {
        $queue = [[$start, []]];
        $visited = [spl_object_id($start) => true];
        for ($index = 0; $index < count($queue); $index++) {
            [$location, $path] = $queue[$index];
            if (count($path) >= $budget) {
                continue;
            }
            foreach ($neighbors($location) as $next) {
                $id = spl_object_id($next);
                if (isset($visited[$id]) || !$traversable($next)) {
                    continue;
                }
                $visited[$id] = true;
                $nextPath = [...$path, $next];
                if (!self::hasAnomaly($next)) {
                    return $nextPath;
                }
                $queue[] = [$next, $nextPath];
            }
        }
        return [];
    }

    /**
     * @param list<Map|StarSystemMap> $path
     * @param callable(Location): bool $traversable
     */
    public static function nextStep(array &$path, callable $traversable): Map|StarSystemMap|null
    {
        $next = array_shift($path);
        return $next !== null && $traversable($next) ? $next : null;
    }
}
