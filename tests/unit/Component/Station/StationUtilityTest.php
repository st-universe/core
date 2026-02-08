<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use Mockery\MockInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Station\Lib\Creation\StationCreatorInterface;
use Stu\Orm\Entity\Station;
use Stu\Orm\Repository\ConstructionProgressModuleRepositoryInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\StuTestCase;

class StationUtilityTest extends StuTestCase
{
    private SpacecraftBuildplanRepositoryInterface&MockInterface $spacecraftBuildplanRepository;
    private ConstructionProgressRepositoryInterface&MockInterface $constructionProgressRepository;
    private ConstructionProgressModuleRepositoryInterface&MockInterface $constructionProgressModuleRepository;
    private StationCreatorInterface&MockInterface $stationCreator;
    private StationRepositoryInterface&MockInterface $stationRepository;
    private StorageManagerInterface&MockInterface $storageManager;
    private SpacecraftRumpRepositoryInterface&MockInterface $spacecraftRumpRepository;
    private TradePostRepositoryInterface&MockInterface $tradePostRepository;
    private TradeLicenseRepositoryInterface&MockInterface $tradeLicenseRepository;
    private StuRandom&MockInterface $stuRandom;

    private StationUtilityInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->spacecraftBuildplanRepository = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $this->constructionProgressRepository = $this->mock(ConstructionProgressRepositoryInterface::class);
        $this->constructionProgressModuleRepository = $this->mock(ConstructionProgressModuleRepositoryInterface::class);
        $this->stationCreator = $this->mock(StationCreatorInterface::class);
        $this->stationRepository = $this->mock(StationRepositoryInterface::class);
        $this->storageManager = $this->mock(StorageManagerInterface::class);
        $this->spacecraftRumpRepository = $this->mock(SpacecraftRumpRepositoryInterface::class);
        $this->tradePostRepository = $this->mock(TradePostRepositoryInterface::class);
        $this->tradeLicenseRepository = $this->mock(TradeLicenseRepositoryInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->subject = new StationUtility(
            $this->spacecraftBuildplanRepository,
            $this->constructionProgressRepository,
            $this->constructionProgressModuleRepository,
            $this->stationCreator,
            $this->stationRepository,
            $this->storageManager,
            $this->spacecraftRumpRepository,
            $this->tradePostRepository,
            $this->tradeLicenseRepository,
            $this->stuRandom,
            $this->initLoggerUtil()
        );
    }

    public function testGetDockedWorkbeeCount(): void
    {
        $station = $this->mock(Station::class);

        $station->shouldReceive('getDockedWorkbeeCount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);


        $result = $this->subject->getDockedWorkbeeCount($station);

        $this->assertEquals(42, $result);
    }
}
