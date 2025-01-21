<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use RuntimeException;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

final class PanelBoundaries
{
    public function __construct(private int $minX, private int $maxX, private int $minY, private int $maxY, private LayerInterface|StarSystemInterface $parent) {}

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
        return $this->parent instanceof LayerInterface;
    }

    public function extendBy(LocationInterface $location): void
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
    public static function fromArray(array $array, LayerInterface $layer): PanelBoundaries
    {
        return new PanelBoundaries(
            $array['minx'],
            $array['maxx'],
            $array['miny'],
            $array['maxy'],
            $layer
        );
    }

    public static function fromSystem(StarSystemInterface $system): PanelBoundaries
    {
        return new PanelBoundaries(
            1,
            $system->getMaxX(),
            1,
            $system->getMaxY(),
            $system
        );
    }

    public static function fromLocation(LocationInterface $location, int $range): PanelBoundaries
    {
        if ($location instanceof MapInterface) {
            return self::fromMap($location, $range);
        }
        if ($location instanceof StarSystemMapInterface) {
            return self::fromSystemMap($location, $range);
        }

        throw new RuntimeException('unsupported location type');
    }

    private static function fromMap(MapInterface $map, int $range): PanelBoundaries
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

    private static function fromSystemMap(StarSystemMapInterface $systemMap, int $range): PanelBoundaries
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
        LocationInterface $location,
        int $width,
        int $height,
        LayerInterface|StarSystemInterface $parent,
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
