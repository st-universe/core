<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use JBBCode\Parser;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Orm\Entity\LayerInterface;
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

    private TradePostRepositoryInterface $tradePostRepository;

    private EncodedMapInterface $encodedMap;

    private Parser $parser;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        TradePostRepositoryInterface $tradePostRepository,
        EncodedMapInterface $encodedMap,
        Parser $parser,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->mapRepository = $mapRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->encodedMap = $encodedMap;
        $this->parser = $parser;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->loggerUtilFactory = $loggerUtilFactory;
    }

    public function createMapSectionHelper(): MapSectionHelper
    {
        return new MapSectionHelper(
            $this,
            $this->loggerUtilFactory
        );
    }

    public function createYRow(
        ?LayerInterface $layer,
        int $cury,
        int $minx,
        int $maxx,
        int|StarSystemInterface $system
    ): YRow {
        return new YRow(
            $this->mapRepository,
            $this->starSystemMapRepository,
            $this->encodedMap,
            $layer,
            $cury,
            $minx,
            $maxx,
            $system
        );
    }

    public function createUserYRow(
        UserInterface $user,
        LayerInterface $layer,
        int $cury,
        int $minx,
        int $maxx,
        int $systemId = 0
    ): UserYRow {
        return new UserYRow(
            $this,
            $this->mapRepository,
            $this->starSystemMapRepository,
            $this->encodedMap,
            $user,
            $layer,
            $cury,
            $minx,
            $maxx,
            $systemId
        );
    }

    public function createExplorableStarmapItem(
        ExploreableStarMapInterface $exploreableStarMap,
        LayerInterface $layer
    ): ExplorableStarMapItem {
        return new ExplorableStarMapItem(
            $this->tradePostRepository,
            $this->encodedMap,
            $this->parser,
            $exploreableStarMap,
            $layer
        );
    }
}
