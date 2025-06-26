<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use RuntimeException;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;

final class PanelBoundaries
{
    public function __construct(private int $minX, private int $maxX, private int $minY, private int $maxY, private Layer|StarSystem $parent) {}

    /** @return array<int> */
    public function getColumnRange(): array
    {
        return range($this->minX, $this->maxX);
    }

    /** @return array<int> */
    public function getRowRange(): array
    {
        return range($this->minY, $this->maxY);
    }

    public function getMinX(): int
    {
        return $this->minX;
    }

    public function getMaxX(): int
    {
        return $this->maxX;
    }

    public function getMinY(): int
    {
        return $this->minY;
    }

    public function getMaxY(): int
    {
        return $this->maxY;
    }

    public function getParentId(): int
    {
        return $this->parent->getId();
    }

    public function isOnMap(): bool
    {
        return $this->parent instanceof Layer;
    }

    public function extendBy(Location $location): void
    {
        $this->minX = min($this->minX, $location->getX());
        $this->maxX = max($this->maxX, $location->getX());
        $this->minY = min($this->minY, $location->getY());
        $this->maxY = max($this->maxY, $location->getY());
    }

    public function stretch(int $amount): void
    {
        $this->minX -= $amount;
        $this->maxX += $amount;
        $this->minY -= $amount;
        $this->maxY += $amount;
    }

    /**
     * @param array{minx: int, maxx: int, miny: int, maxy: int} $array
     */
    public static function fromArray(array $array, Layer $layer): PanelBoundaries
    {
        return new PanelBoundaries(
            $array['minx'],
            $array['maxx'],
            $array['miny'],
            $array['maxy'],
            $layer
        );
    }

    public static function fromSystem(StarSystem $system): PanelBoundaries
    {
        return new PanelBoundaries(
            1,
            $system->getMaxX(),
            1,
            $system->getMaxY(),
            $system
        );
    }

    public static function fromLocation(Location $location, int $range): PanelBoundaries
    {
        if ($location instanceof Map) {
            return self::fromMap($location, $range);
        }
        if ($location instanceof StarSystemMap) {
            return self::fromSystemMap($location, $range);
        }

        throw new RuntimeException('unsupported location type');
    }

    private static function fromMap(Map $map, int $range): PanelBoundaries
    {
        $layer = $map->getLayer();
        if ($layer === null) {
            throw new RuntimeException('this should not happen');
        }

        return self::createLocationWithRange(
            $map,
            $layer->getWidth(),
            $layer->getHeight(),
            $layer,
            $range
        );
    }

    private static function fromSystemMap(StarSystemMap $systemMap, int $range): PanelBoundaries
    {
        return self::createLocationWithRange(
            $systemMap,
            $systemMap->getSystem()->getMaxX(),
            $systemMap->getSystem()->getMaxY(),
            $systemMap->getSystem(),
            $range
        );
    }

    private static function createLocationWithRange(
        Location $location,
        int $width,
        int $height,
        Layer|StarSystem $parent,
        int $range
    ): PanelBoundaries {
        return new PanelBoundaries(
            max(1, $location->getX() - $range),
            min($width, $location->getX() + $range),
            max(1, $location->getY() - $range),
            min($height, $location->getY() + $range),
            $parent
        );
    }
}
