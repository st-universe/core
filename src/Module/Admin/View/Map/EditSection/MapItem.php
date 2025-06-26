<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\EditSection;

use RuntimeException;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Orm\Entity\Map;

class MapItem
{
    public function __construct(private EncodedMapInterface $encodedMap, private Map $map) {}

    public function getMap(): Map
    {
        return $this->map;
    }

    public function getMapGraphicPath(): string
    {
        $layer = $this->map->getLayer();
        if ($layer === null) {
            throw new RuntimeException('this should not happen');
        }

        if ($layer->isEncoded()) {
            return $this->encodedMap->getEncodedMapPath(
                $this->map->getFieldId(),
                $layer
            );
        }
        return sprintf('%d/%d.png', $layer->getId(), $this->map->getFieldId());
    }
}
