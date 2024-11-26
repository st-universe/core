<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class DatabaseUiFactoryTest extends StuTestCase
{
    /** @var MockInterface&CommodityRepositoryInterface */
    private MockInterface $commodityRepository;

    /** @var MockInterface&ShipRepositoryInterface */
    private MockInterface $shipRepository;

    /** @var MockInterface&ColonyRepositoryInterface */
    private MockInterface $colonyRepository;

    /** @var MockInterface&TradePostRepositoryInterface */
    private MockInterface $tradePostRepository;

    /** @var MockInterface&UserRepositoryInterface */
    private MockInterface $userRepository;

    private DatabaseUiFactory $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->commodityRepository = $this->mock(CommodityRepositoryInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->tradePostRepository = $this->mock(TradePostRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->subject = new DatabaseUiFactory(
            $this->commodityRepository,
            $this->shipRepository,
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
