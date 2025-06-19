<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\StuTestCase;

class CommodityTransferTest extends StuTestCase
{
    private MockInterface&StorageManagerInterface $storageManager;
    private MockInterface&ColonyRepositoryInterface $colonyRepository;

    private MockInterface&InformationWrapper $informations;

    private CommodityTransferInterface $subject;


    #[Override]
    protected function setUp(): void
    {
        $this->storageManager = $this->mock(StorageManagerInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);

        $this->informations = $this->mock(InformationWrapper::class);

        $this->subject = new CommodityTransfer(
            $this->storageManager,
            $this->colonyRepository
        );
    }

    public function testTransferCommodityExpectNothingWhenCommodityNotPresent(): void
    {
        $colony = $this->mock(ColonyInterface::class);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $result = $this->subject->transferCommodity(
            42,
            999,
            $colony,
            $colony,
            $colony,
            $this->informations
        );

        $this->assertFalse($result);
    }

    public function testTransferCommodityExpectInfoWhenCommodityNotBeamable(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $storage = $this->mock(StorageInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $user = $this->mock(UserInterface::class);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));
        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $storage->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);

        $commodity->shouldReceive('isBeamable')
            ->with($user, $user)
            ->once()
            ->andReturn(false);
        $commodity->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('COMMODITY');

        $this->informations->shouldReceive('addInformationf')
            ->with('%s ist nicht beambar', 'COMMODITY')
            ->once();

        $result = $this->subject->transferCommodity(
            42,
            999,
            $colony,
            $colony,
            $colony,
            $this->informations
        );

        $this->assertFalse($result);
    }

    public function testTransferCommodityExpectNothingWhenNoEps(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $storage = $this->mock(StorageInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $user = $this->mock(UserInterface::class);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));
        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(0);

        $storage->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);

        $commodity->shouldReceive('isBeamable')
            ->with($user, $user)
            ->once()
            ->andReturn(true);

        $result = $this->subject->transferCommodity(
            42,
            999,
            $colony,
            $colony,
            $colony,
            $this->informations
        );

        $this->assertFalse($result);
    }

    public function testTransferCommodityExpectNothingWhenAmountNotReadable(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $storage = $this->mock(StorageInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $user = $this->mock(UserInterface::class);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));
        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(1);

        $storage->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);

        $commodity->shouldReceive('isBeamable')
            ->with($user, $user)
            ->once()
            ->andReturn(true);

        $result =  $this->subject->transferCommodity(
            42,
            'FOO',
            $colony,
            $colony,
            $colony,
            $this->informations
        );

        $this->assertFalse($result);
    }

    public function testTransferCommodityExpectNothingWhenAmountSmallerOne(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $storage = $this->mock(StorageInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $user = $this->mock(UserInterface::class);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));
        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(1);

        $storage->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);

        $commodity->shouldReceive('isBeamable')
            ->with($user, $user)
            ->once()
            ->andReturn(true);

        $result = $this->subject->transferCommodity(
            42,
            0,
            $colony,
            $colony,
            $colony,
            $this->informations
        );

        $this->assertFalse($result);
    }

    public function testTransferCommodityExpectNothingWhenTargetStorageFull(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $storage = $this->mock(StorageInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $user = $this->mock(UserInterface::class);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));
        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(1);
        $colony->shouldReceive('getStorageSum')
            ->withNoArgs()
            ->andReturn(999);
        $colony->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->andReturn(999);

        $storage->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);

        $commodity->shouldReceive('isBeamable')
            ->with($user, $user)
            ->once()
            ->andReturn(true);

        $result = $this->subject->transferCommodity(
            42,
            1,
            $colony,
            $colony,
            $colony,
            $this->informations
        );

        $this->assertFalse($result);
    }

    public function testTransferCommodityExpectTransferCappedByStorageAmount(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $storage = $this->mock(StorageInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $user = $this->mock(UserInterface::class);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));
        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(6);
        $colony->shouldReceive('getBeamFactor')
            ->withNoArgs()
            ->andReturn(10);
        $colony->shouldReceive('lowerEps')
            ->with(6)
            ->once();

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getStorageSum')
            ->withNoArgs()
            ->andReturn(100);
        $ship->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->andReturn(1000);
        $ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->andReturn(null);

        $storage->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);
        $storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(55);

        $commodity->shouldReceive('isBeamable')
            ->with($user, $user)
            ->once()
            ->andReturn(true);
        $commodity->shouldReceive('getTransferCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $commodity->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('COMMODITY');

        $this->storageManager->shouldReceive('lowerStorage')
            ->with($colony, $commodity, 55)
            ->once();

        $this->storageManager->shouldReceive('upperStorage')
            ->with($ship, $commodity, 55)
            ->once();

        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->informations->shouldReceive('addInformationf')
            ->with('%d %s (Energieverbrauch: %d)', 55, 'COMMODITY', 6)
            ->once();

        $result = $this->subject->transferCommodity(
            42,
            56,
            $colony,
            $colony,
            $ship,
            $this->informations
        );

        $this->assertTrue($result);
    }

    public function testTransferCommodityExpectMaximumTransfer(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $storage = $this->mock(StorageInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $user = $this->mock(UserInterface::class);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));
        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(20);
        $colony->shouldReceive('getBeamFactor')
            ->withNoArgs()
            ->andReturn(10);
        $colony->shouldReceive('lowerEps')
            ->with(10)
            ->once();

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getStorageSum')
            ->withNoArgs()
            ->andReturn(100);
        $ship->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->andReturn(1000);
        $ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->andReturn(null);

        $storage->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);
        $storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn(99);

        $commodity->shouldReceive('isBeamable')
            ->with($user, $user)
            ->once()
            ->andReturn(true);
        $commodity->shouldReceive('getTransferCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $commodity->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('COMMODITY');

        $this->storageManager->shouldReceive('lowerStorage')
            ->with($colony, $commodity, 99)
            ->once();

        $this->storageManager->shouldReceive('upperStorage')
            ->with($ship, $commodity, 99)
            ->once();

        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->informations->shouldReceive('addInformationf')
            ->with('%d %s (Energieverbrauch: %d)', 99, 'COMMODITY', 10)
            ->once();

        $result = $this->subject->transferCommodity(
            42,
            'max',
            $colony,
            $colony,
            $ship,
            $this->informations
        );

        $this->assertTrue($result);
    }

    public function testTransferCommodityExpectTransferCappedByColonyEps(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $storage = $this->mock(StorageInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $user = $this->mock(UserInterface::class);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));
        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(5);
        $colony->shouldReceive('getBeamFactor')
            ->withNoArgs()
            ->andReturn(10);
        $colony->shouldReceive('lowerEps')
            ->with(5)
            ->once();

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getStorageSum')
            ->withNoArgs()
            ->andReturn(100);
        $ship->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->andReturn(1000);
        $ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->andReturn(null);

        $storage->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);
        $storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(55);

        $commodity->shouldReceive('isBeamable')
            ->with($user, $user)
            ->once()
            ->andReturn(true);
        $commodity->shouldReceive('getTransferCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $commodity->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('COMMODITY');

        $this->storageManager->shouldReceive('lowerStorage')
            ->with($colony, $commodity, 50)
            ->once();

        $this->storageManager->shouldReceive('upperStorage')
            ->with($ship, $commodity, 50)
            ->once();

        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->informations->shouldReceive('addInformationf')
            ->with('%d %s (Energieverbrauch: %d)', 50, 'COMMODITY', 5)
            ->once();

        $result = $this->subject->transferCommodity(
            42,
            56,
            $colony,
            $colony,
            $ship,
            $this->informations
        );

        $this->assertTrue($result);
    }

    public function testTransferCommodityExpectTransferCappedByShipEps(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $storage = $this->mock(StorageInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $user = $this->mock(UserInterface::class);
        $epsSystem = $this->mock(EpsSystemData::class);

        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony->shouldReceive('getStorageSum')
            ->withNoArgs()
            ->andReturn(100);
        $colony->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->andReturn(1000);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->andReturn($epsSystem);

        $ship->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));
        $ship->shouldReceive('getRump->getBeamFactor')
            ->withNoArgs()
            ->andReturn(10);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->andReturn(null);

        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(5);
        $epsSystem->shouldReceive('lowerEps')
            ->with(5)
            ->once()
            ->andReturnSelf();
        $epsSystem->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $storage->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);
        $storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(55);

        $commodity->shouldReceive('isBeamable')
            ->with($user, $user)
            ->once()
            ->andReturn(true);
        $commodity->shouldReceive('getTransferCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $commodity->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('COMMODITY');

        $this->storageManager->shouldReceive('upperStorage')
            ->with($colony, $commodity, 50)
            ->once();

        $this->storageManager->shouldReceive('lowerStorage')
            ->with($ship, $commodity, 50)
            ->once();

        $this->informations->shouldReceive('addInformationf')
            ->with('%d %s (Energieverbrauch: %d)', 50, 'COMMODITY', 5)
            ->once();

        $result = $this->subject->transferCommodity(
            42,
            56,
            $wrapper,
            $ship,
            $colony,
            $this->informations
        );

        $this->assertTrue($result);
    }

    public function testTransferCommodityExpectTransferCappedByFreeStorage(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $storage = $this->mock(StorageInterface::class);
        $commodity = $this->mock(CommodityInterface::class);
        $user = $this->mock(UserInterface::class);

        $colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([42 => $storage]));
        $colony->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $colony->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(5);
        $colony->shouldReceive('getBeamFactor')
            ->withNoArgs()
            ->andReturn(10);
        $colony->shouldReceive('lowerEps')
            ->with(1)
            ->once();

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getStorageSum')
            ->withNoArgs()
            ->andReturn(90);
        $ship->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->andReturn(100);
        $ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->andReturn(null);

        $storage->shouldReceive('getCommodity')
            ->withNoArgs()
            ->once()
            ->andReturn($commodity);
        $storage->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(55);

        $commodity->shouldReceive('isBeamable')
            ->with($user, $user)
            ->once()
            ->andReturn(true);
        $commodity->shouldReceive('getTransferCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $commodity->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('COMMODITY');

        $this->storageManager->shouldReceive('lowerStorage')
            ->with($colony, $commodity, 10)
            ->once();

        $this->storageManager->shouldReceive('upperStorage')
            ->with($ship, $commodity, 10)
            ->once();

        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->informations->shouldReceive('addInformationf')
            ->with('%d %s (Energieverbrauch: %d)', 10, 'COMMODITY', 1)
            ->once();

        $result = $this->subject->transferCommodity(
            42,
            56,
            $colony,
            $colony,
            $ship,
            $this->informations
        );

        $this->assertTrue($result);
    }
}
