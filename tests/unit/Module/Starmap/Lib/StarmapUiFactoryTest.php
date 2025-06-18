<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use JBBCode\Parser;
use Mockery\MockInterface;
use Override;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\StuTestCase;

class StarmapUiFactoryTest extends StuTestCase
{
    private MapRepositoryInterface&MockInterface $mapRepository;
    private StarSystemMapRepositoryInterface&MockInterface $starSystemMapRepository;
    private MockInterface&TradePostRepositoryInterface $tradePostRepository;
    private MockInterface&UserSettingsProviderInterface $userSettingsProvider;
    private MockInterface&EncodedMapInterface $encodedMap;
    private MockInterface&Parser $bbCodeParser;

    private StarmapUiFactory $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->mapRepository = $this->mock(MapRepositoryInterface::class);
        $this->tradePostRepository = $this->mock(TradePostRepositoryInterface::class);
        $this->starSystemMapRepository = $this->mock(StarSystemMapRepositoryInterface::class);
        $this->userSettingsProvider = $this->mock(UserSettingsProviderInterface::class);
        $this->encodedMap = $this->mock(EncodedMapInterface::class);
        $this->bbCodeParser = $this->mock(Parser::class);

        $this->subject = new StarmapUiFactory(
            $this->mapRepository,
            $this->tradePostRepository,
            $this->starSystemMapRepository,
            $this->userSettingsProvider,
            $this->encodedMap,
            $this->bbCodeParser,
            $this->initLoggerUtil()
        );
    }

    public function testCreateMapSectionHelperCreates(): void
    {
        static::assertInstanceOf(
            MapSectionHelper::class,
            $this->subject->createMapSectionHelper()
        );
    }

    public function testCreateYRowCreates(): void
    {
        static::assertInstanceOf(
            YRow::class,
            $this->subject->createYRow(
                $this->mock(LayerInterface::class),
                222,
                333,
                444,
                555
            )
        );
    }

    public function testCreateUserYRowCreates(): void
    {
        static::assertInstanceOf(
            UserYRow::class,
            $this->subject->createUserYRow(
                $this->mock(UserInterface::class),
                $this->mock(LayerInterface::class),
                111,
                222,
                333,
                444
            )
        );
    }

    public function testCreateExplorableStarmapItem(): void
    {
        static::assertInstanceOf(
            ExplorableStarMapItem::class,
            $this->subject->createExplorableStarmapItem(
                $this->mock(ExploreableStarMapInterface::class),
                $this->mock(LayerInterface::class)
            )
        );
    }
}
