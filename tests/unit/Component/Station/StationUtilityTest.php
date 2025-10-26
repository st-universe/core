<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Station\Lib\Creation\StationCreatorInterface;
use Stu\Orm\Entity\Ship;
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
        $docked1 = $this->mock(Ship::class);
        $docked2 = $this->mock(Ship::class);
        $docked3 = $this->mock(Ship::class);
        $docked4 = $this->mock(Ship::class);

        $station->shouldReceive('getDockedShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$docked1, $docked2, $docked3, $docked4]));

        $docked1->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $docked2->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $docked2->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $docked3->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $docked3->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $docked3->shouldReceive('getRump->isWorkbee')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $docked4->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $docked4->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $docked4->shouldReceive('getRump->isWorkbee')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->getDockedWorkbeeCount($station);

        $this->assertEquals(1, $result);
    }
}
