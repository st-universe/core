<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class YRow
{
    protected int $layerId;

    protected int $row;

    protected int $minx;

    protected int $maxx;

    protected int $systemId;

    /** @var null|array<MapInterface|null>|array<StarSystemMapInterface|null>|array<ExploreableStarMapInterface|null> */
    protected $fields = null;

    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $systemMapRepository;

    function __construct(
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $systemMapRepository,
        int $layerId,
        int $cury,
        int $minx,
        int $maxx,
        int $systemId = 0
    ) {
        $this->layerId = $layerId;
        $this->row = $cury;
        $this->minx = $minx;
        $this->maxx = $maxx;
        $this->systemId = $systemId;
        $this->mapRepository = $mapRepository;
        $this->systemMapRepository = $systemMapRepository;
    }

    /**
     * @return array<MapInterface|null>|array<StarSystemMapInterface|null>|array<ExploreableStarMapInterface|null>
     */
    function getFields()
    {
        if ($this->fields === null) {
            $this->fields = [];
            for ($i = $this->minx; $i <= $this->maxx; $i++) {
                $this->fields[] = $this->mapRepository->getByCoordinates(
                    $this->layerId,
                    $i,
                    $this->row
                );
            }
        }
        return $this->fields;
    }

    /**
     * @return array<MapInterface|null>|array<StarSystemMapInterface|null>|array<ExploreableStarMapInterface|null>
     */
    function getSystemFields()
    {
        if ($this->fields === null) {
            $this->fields = [];
            for ($i = $this->minx; $i <= $this->maxx; $i++) {
                $this->fields[] = $this->systemMapRepository->getByCoordinates(
                    $this->systemId,
                    $i,
                    $this->row
                );
            }
        }
        return $this->fields;
    }

    /**
     * @return int
     */
    function getRow()
    {
        return $this->row;
    }
}
