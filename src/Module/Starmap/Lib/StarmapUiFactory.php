<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use JBBCode\Parser;
use Override;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
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
    public function __construct(
        private readonly MapRepositoryInterface $mapRepository,
        private readonly TradePostRepositoryInterface $tradePostRepository,
        private readonly StarSystemMapRepositoryInterface $starSystemMapRepository,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly EncodedMapInterface $encodedMap,
        private readonly Parser $parser,
        private readonly LoggerUtilFactoryInterface $loggerUtilFactory
    ) {}

    #[Override]
    public function createMapSectionHelper(): MapSectionHelper
    {
        return new MapSectionHelper(
            $this,
            $this->loggerUtilFactory
        );
    }

    #[Override]
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

    #[Override]
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

    #[Override]
    public function createExplorableStarmapItem(
        ExploreableStarMapInterface $exploreableStarMap,
        LayerInterface $layer
    ): ExplorableStarMapItem {
        return new ExplorableStarMapItem(
            $this->tradePostRepository,
            $this->userSettingsProvider,
            $this->encodedMap,
            $this->parser,
            $exploreableStarMap,
            $layer
        );
    }
}
