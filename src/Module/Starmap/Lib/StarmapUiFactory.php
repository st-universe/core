<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use JBBCode\Parser;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\User;
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

    #[\Override]
    public function createMapSectionHelper(): MapSectionHelper
    {
        return new MapSectionHelper(
            $this,
            $this->loggerUtilFactory
        );
    }

    #[\Override]
    public function createYRow(
        ?Layer $layer,
        int $cury,
        int $minx,
        int $maxx,
        int|StarSystem $system
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

    #[\Override]
    public function createUserYRow(
        User $user,
        Layer $layer,
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

    #[\Override]
    public function createExplorableStarmapItem(
        ExploreableStarMapInterface $exploreableStarMap,
        Layer $layer
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
