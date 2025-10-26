<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Mockery\MockInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class DatabaseUiFactoryTest extends StuTestCase
{
    private MockInterface&CommodityRepositoryInterface $commodityRepository;
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;
    private MockInterface&ColonyRepositoryInterface $colonyRepository;
    private MockInterface&TradePostRepositoryInterface $tradePostRepository;
    private MockInterface&UserRepositoryInterface $userRepository;

    private DatabaseUiFactory $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->commodityRepository = $this->mock(CommodityRepositoryInterface::class);
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->tradePostRepository = $this->mock(TradePostRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->subject = new DatabaseUiFactory(
            $this->commodityRepository,
            $this->spacecraftRepository,
            $this->colonyRepository,
            $this->userRepository,
            $this->tradePostRepository
        );
    }

    public function testCreateStorageWrapperReturnsInstance(): void
    {
        static::assertInstanceOf(
            StorageWrapper::class,
            $this->subject->createStorageWrapper(666, 42, 33)
        );
    }

    public function testCreateDatabaseTopActivTradePostReturnsInstance(): void
    {
        static::assertInstanceOf(
            DatabaseTopActivTradePost::class,
            $this->subject->createDatabaseTopActivTradePost(['id' => 666])
        );
    }

    public function testCreateDatabaseTopListCrewReturnsInstance(): void
    {
        static::assertInstanceOf(
            DatabaseTopListCrew::class,
            $this->subject->createDatabaseTopListCrew(['user_id' => 42])
        );
    }

    public function testCreateDatabaseTopListWithPointsReturnsInstance(): void
    {
        static::assertInstanceOf(
            DatabaseTopListWithPoints::class,
            $this->subject->createDatabaseTopListWithPoints(42, '666')
        );
    }

    public function testCreateDatabaseTopListFlightReturnsInstance(): void
    {
        static::assertInstanceOf(
            DatabaseTopListFlights::class,
            $this->subject->createDatabaseTopListFlights(['user_id' => 666])
        );
    }
}
