<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

use Mockery\MockInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
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

    private DatabaseUiFactory $subject;

    protected function setUp(): void
    {
        $this->commodityRepository = $this->mock(CommodityRepositoryInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->tradePostRepository = $this->mock(TradePostRepositoryInterface::class);

        $this->subject = new DatabaseUiFactory(
            $this->commodityRepository,
            $this->shipRepository,
            $this->colonyRepository,
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
}
