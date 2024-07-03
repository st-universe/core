<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Stu\Lib\Map\Location;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemInterface;

final class PanelBoundaries
{
    public function __construct(private int $minX, private int $maxX, private int $minY, private int $maxY, private LayerInterface|StarSystemInterface $parent)
    {
    }

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

    public static function fromLocation(Location $location, int $range): PanelBoundaries
    {
        $map = $location->get();

        $width = $map instanceof MapInterface ? $map->getLayer()->getWidth() : $map->getSystem()->getMaxX();
        $height = $map instanceof MapInterface ? $map->getLayer()->getHeight() : $map->getSystem()->getMaxY();
        $parent = $map instanceof MapInterface ? $map->getLayer() : $map->getSystem();

        return new PanelBoundaries(
            max(1, $map->getX() - $range),
            min($width, $map->getX() + $range),
            max(1, $map->getY() - $range),
            min($height, $map->getY() + $range),
            $parent
        );
    }
}
