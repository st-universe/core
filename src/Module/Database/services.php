<?php

declare(strict_types=1);

namespace Stu\Module\Database;

use Stu\Module\Control\GameController;
use Stu\Module\Database\Action\DeleteColonyScan;
use Stu\Module\Database\Lib\CreateDatabaseEntry;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Database\Lib\DatabaseUiFactory;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Module\Database\View\Category\Category;
use Stu\Module\Database\View\Category\CategoryRequest;
use Stu\Module\Database\View\Category\CategoryRequestInterface;
use Stu\Module\Database\View\Category\Wrapper\DatabaseCategoryWrapperFactory;
use Stu\Module\Database\View\Category\Wrapper\DatabaseCategoryWrapperFactoryInterface;
use Stu\Module\Database\View\ColonyProductionWorthRanking\ColonyProductionWorthRanking;
use Stu\Module\Database\View\ColonyWorthRanking\ColonyWorthRanking;
use Stu\Module\Database\View\CommoditiesOverview\CommoditiesOverview;
use Stu\Module\Database\View\CrewRanking\CrewRanking;
use Stu\Module\Database\View\DatabaseEntry\DatabaseEntry;
use Stu\Module\Database\View\DatabaseEntry\DatabaseEntryRequest;
use Stu\Module\Database\View\DatabaseEntry\DatabaseEntryRequestInterface;
use Stu\Module\Database\View\DiscovererRating\DiscovererRanking;
use Stu\Module\Database\View\FlightRanking\FlightRanking;
use Stu\Module\Database\View\LatinumRanking\LatinumRanking;
use Stu\Module\Database\View\PirateWrathRanking\PirateWrathRanking;
use Stu\Module\Database\View\ResearchRanking\ResearchRanking;
use Stu\Module\Database\View\RpgRanking\RpgRanking;
use Stu\Module\Database\View\SatisfiedWorkerRanking\SatisfiedWorkerRanking;
use Stu\Module\Database\View\ShowColonySurface\ShowColonySurface;
use Stu\Module\Database\View\ShowColonySurface\ShowColonySurfaceRequest;
use Stu\Module\Database\View\ShowColonySurface\ShowColonySurfaceRequestInterface;
use Stu\Module\Database\View\ShowCommoditiesLocations\ShowCommoditiesLocations;
use Stu\Module\Database\View\ShowCommoditiesLocations\ShowCommoditiesLocationsRequest;
use Stu\Module\Database\View\ShowCommoditiesLocations\ShowCommoditiesLocationsRequestInterface;
use Stu\Module\Database\View\ShowPrestigeLog\ShowPrestigeLog;
use Stu\Module\Database\View\ShowStatistics\ShowStatistics;
use Stu\Module\Database\View\TradePostActivity\TradePostActivity;
use Stu\Module\Database\View\NPCList\NPCList;
use Stu\Module\Database\View\NPCList\NPCListRequest;
use Stu\Module\Database\View\NPCList\NPCListRequestInterface;
use Stu\Module\Database\View\UserList\UserList;
use Stu\Module\Database\View\UserList\UserListRequest;
use Stu\Module\Database\View\UserList\UserListRequestInterface;
use Stu\Module\Game\View\Overview\Overview;

use function DI\autowire;

return [
    CreateDatabaseEntryInterface::class => autowire(CreateDatabaseEntry::class),
    DatabaseCategoryWrapperFactoryInterface::class => autowire(DatabaseCategoryWrapperFactory::class),
    DatabaseEntryRequestInterface::class => autowire(DatabaseEntryRequest::class),
    CategoryRequestInterface::class => autowire(CategoryRequest::class),
    ShowColonySurfaceRequestInterface::class => autowire(ShowColonySurfaceRequest::class),
    ShowCommoditiesLocationsRequestInterface::class => autowire(ShowCommoditiesLocationsRequest::class),
    UserListRequestInterface::class => autowire(UserListRequest::class),
    NPCListRequestInterface::class => autowire(NPCListRequest::class),
    'DATABASE_ACTIONS' => [
        DeleteColonyScan::ACTION_IDENTIFIER => autowire(DeleteColonyScan::class)
    ],
    'DATABASE_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        Category::VIEW_IDENTIFIER => autowire(Category::class),
        CrewRanking::VIEW_IDENTIFIER => autowire(CrewRanking::class),
        DatabaseEntry::VIEW_IDENTIFIER => autowire(DatabaseEntry::class),
        DiscovererRanking::VIEW_IDENTIFIER => autowire(DiscovererRanking::class),
        FlightRanking::VIEW_IDENTIFIER => autowire(FlightRanking::class),
        ColonyWorthRanking::VIEW_IDENTIFIER => autowire(ColonyWorthRanking::class),
        ColonyProductionWorthRanking::VIEW_IDENTIFIER => autowire(ColonyProductionWorthRanking::class),
        CommoditiesOverview::VIEW_IDENTIFIER => autowire(CommoditiesOverview::class),
        LatinumRanking::VIEW_IDENTIFIER => autowire(LatinumRanking::class),
        ResearchRanking::VIEW_IDENTIFIER => autowire(ResearchRanking::class),
        PirateWrathRanking::VIEW_IDENTIFIER => autowire(PirateWrathRanking::class),
        RpgRanking::VIEW_IDENTIFIER => autowire(RpgRanking::class),
        SatisfiedWorkerRanking::VIEW_IDENTIFIER => autowire(SatisfiedWorkerRanking::class),
        ShowColonySurface::VIEW_IDENTIFIER => autowire(ShowColonySurface::class),
        ShowCommoditiesLocations::VIEW_IDENTIFIER => autowire(ShowCommoditiesLocations::class),
        ShowPrestigeLog::VIEW_IDENTIFIER => autowire(ShowPrestigeLog::class),
        ShowStatistics::VIEW_IDENTIFIER => autowire(ShowStatistics::class),
        TradePostActivity::VIEW_IDENTIFIER => autowire(TradePostActivity::class),
        UserList::VIEW_IDENTIFIER => autowire(UserList::class),
        NPCList::VIEW_IDENTIFIER => autowire(NPCList::class)
    ],
    DatabaseUiFactoryInterface::class => autowire(DatabaseUiFactory::class),
];