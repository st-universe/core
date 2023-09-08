<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\EditSection;

use Stu\Component\Map\EncodedMapInterface;
use Stu\Orm\Entity\MapInterface;

class StarMapItem
{
    private EncodedMapInterface $encodedMap;

    private MapInterface $map;

    public function __construct(
        EncodedMapInterface $encodedMap,
        MapInterface $map
    ) {
        $this->encodedMap = $encodedMap;
        $this->map = $map;
    }

    public function getMap(): MapInterface
    {
        return $this->map;
    }

    public function getMapGraphicPath(): string
    {
        $layer = $this->map->getLayer();

        if ($layer->isEncoded()) {
            return $this->encodedMap->getEncodedMapPath(
                $this->map->getFieldId(),
                $layer
            );
        }
        return sprintf('%d/%d.png', $layer->getId(), $this->map->getFieldId());
    }
}
