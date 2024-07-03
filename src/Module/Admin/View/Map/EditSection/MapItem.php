<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\EditSection;

use Stu\Component\Map\EncodedMapInterface;
use Stu\Orm\Entity\MapInterface;

class MapItem
{
    public function __construct(private EncodedMapInterface $encodedMap, private MapInterface $map)
    {
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
