<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use JBBCode\Parser;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

/**
 * Creates ui and starmap related items
 */
final class StarmapUiFactory implements StarmapUiFactoryInterface
{
    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private Parser $parser;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        TradePostRepositoryInterface $tradePostRepository,
        Parser $parser,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->parser = $parser;
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
        int|StarSystemInterface $system
    ): YRow {
        return new YRow(
            $this->mapRepository,
            $this->starSystemMapRepository,
            $layerId,
            $cury,
            $minx,
            $maxx,
            $system
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
            $this,
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

    public function createExplorableStarmapItem(
        ExploreableStarMapInterface $exploreableStarMap
    ): ExplorableStarMapItem {
        return new ExplorableStarMapItem(
            $this->tradePostRepository,
            $this->parser,
            $exploreableStarMap
        );
    }
}
