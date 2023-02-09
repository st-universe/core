<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

/**
 * Creates ui and starmap related items
 */
final class StarmapUiFactory implements StarmapUiFactoryInterface
{
    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    )
    {
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
    }

    public function createMapSectionHelper(): MapSectionHelper
    {
        return new MapSectionHelper(
            $this
        );
    }

    public function createYRow(
        int $layerId,
        int $cury,
        int $minx,
        int $maxx,
        int $systemId = 0
    ): YRow {
        return new YRow(
            $this->mapRepository,
            $this->starSystemMapRepository,
            $layerId,
            $cury,
            $minx,
            $maxx,
            $systemId
        );
    }

    public function createUserYRow(
        UserInterface $user,
        int $layerId,
        int $cury,
        int $minx,
        int $maxx,
        int $systemId = 0
    ): UserYRow {
        return new UserYRow(
            $this->mapRepository,
            $this->starSystemMapRepository,
            $user,
            $layerId,
            $cury,
            $minx,
            $maxx,
            $systemId
        );
    }
}