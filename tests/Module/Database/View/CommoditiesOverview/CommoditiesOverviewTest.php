<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\CommoditiesOverview;

use Override;
use Mockery\MockInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Module\Database\Lib\StorageWrapper;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\StuTestCase;

class CommoditiesOverviewTest extends StuTestCase
{
    /** @var MockInterface&StorageRepositoryInterface */
    private MockInterface $storageRepository;

    /** @var MockInterface&DatabaseUiFactoryInterface */
    private MockInterface $databaseUiFactory;

    private CommoditiesOverview $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);
        $this->databaseUiFactory = $this->mock(DatabaseUiFactoryInterface::class);

        $this->subject = new CommoditiesOverview(
            $this->storageRepository,
            $this->databaseUiFactory
        );
    }

    public function testHandleRenders(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(UserInterface::class);
        $storageWrapper = $this->mock(StorageWrapper::class);

        $commodityId = 666;
        $amount = 42;

        $game->shouldReceive('setNavigation')
            ->with([
                [
                    'url' => 'database.php',
                    'title' => 'Datenbank'
                ],
                [
                    'url' => sprintf('database.php?%s=1', CommoditiesOverview::VIEW_IDENTIFIER),
                    'title' => 'Warenübersicht'
                ]
            ])
            ->once();
        $game->shouldReceive('setPageTitle')
            ->with('/ Datenbank / Warenübersicht')
            ->once();
        $game->shouldReceive('showMacro')
            ->with('html/database.xhtml/commodities_overview')
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with(
                'COMMODITIES_LIST',
                [$storageWrapper]
            )
            ->once();
        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->storageRepository->shouldReceive('getByUserAccumulated')
            ->with($user)
            ->once()
            ->andReturn([['commodity_id' => $commodityId, 'amount' => $amount]]);

        $this->databaseUiFactory->shouldReceive('createStorageWrapper')
            ->with($commodityId, $amount)
            ->once()
            ->andReturn($storageWrapper);

        $this->subject->handle($game);
    }
}
