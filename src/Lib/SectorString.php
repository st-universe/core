<?php

namespace Stu\Lib;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

class SectorString
{
    public static function getForMap(MapInterface $map): string
    {
        return  $map->getCx() . '|' . $map->getCy();
    }

    public static function getForStarSystemMap(StarSystemMapInterface $systemMap): string
    {
        return sprintf(
            '%d|%d (%s-%s)',
            $systemMap->getSx(),
            $systemMap->getSy(),
            $systemMap->getSystem()->getName(),
            $systemMap->getSystem()->isWormhole() ? 'Wurmloch' : 'System'
        );
    }
}
