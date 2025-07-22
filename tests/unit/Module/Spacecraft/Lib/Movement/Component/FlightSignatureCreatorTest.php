<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Map\DirectionEnum;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\StuTestCase;

class FlightSignatureCreatorTest extends StuTestCase
{
    private MockInterface&FlightSignatureRepositoryInterface $flightSignatureRepository;
    private MockInterface&StuTime $stuTime;
    private MockInterface&ShipLoaderInterface $shipLoader;
    private MockInterface&SpacecraftRumpRepositoryInterface $spacecraftRumpRepository;

    private FlightSignatureCreator $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->flightSignatureRepository = $this->mock(FlightSignatureRepositoryInterface::class);
        $this->stuTime = $this->mock(StuTime::class);
        $this->shipLoader = $this->mock(ShipLoaderInterface::class);
        $this->spacecraftRumpRepository = $this->mock(SpacecraftRumpRepositoryInterface::class);

        $this->subject = new FlightSignatureCreator(
            $this->flightSignatureRepository,
            $this->stuTime,
            $this->shipLoader,
            $this->spacecraftRumpRepository
        );
    }

    public function testCreateSignaturesExpectExceptionWhenDifferentType1(): void
    {
        static::expectExceptionMessage('wayopints have different type');
        static::expectException(InvalidArgumentException::class);

        $this->subject->createSignatures(
            $this->mock(Ship::class),
            DirectionEnum::LEFT,
            $this->mock(Map::class),
            $this->mock(StarSystemMap::class)
        );
    }

    public function testCreateSignaturesExpectExceptionWhenDifferentType2(): void
    {
        static::expectExceptionMessage('wayopints have different type');
        static::expectException(InvalidArgumentException::class);

        $this->subject->createSignatures(
            $this->mock(Ship::class),
            DirectionEnum::LEFT,
            $this->mock(StarSystemMap::class),
            $this->mock(Map::class)
        );
    }

    public static function directionDataProvider(): array
    {
        return [
            [DirectionEnum::RIGHT, DirectionEnum::LEFT],
            [DirectionEnum::LEFT, DirectionEnum::RIGHT],
            [DirectionEnum::TOP, DirectionEnum::BOTTOM],
            [DirectionEnum::BOTTOM, DirectionEnum::TOP],
        ];
    }

    #[DataProvider('directionDataProvider')]
    public function testCreateSignaturesCreatesForMapFields(
        DirectionEnum $fromDirection,
        DirectionEnum $toDirection
    ): void {
        $ship = $this->mock(Ship::class);
        $currentField = $this->mock(Map::class);
        $nextField = $this->mock(Map::class);
        $fromSignature = $this->mock(FlightSignature::class);
        $toSignature = $this->mock(FlightSignature::class);
        $shipRump = $this->mock(SpacecraftRump::class);

        $userId = 666;
        $shipId = 42;
        $shipName = 'some-name';
        $cloakState = true;
        $ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->times(4)
            ->andReturn($userId);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->times(4)
            ->andReturn($shipId);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipName);
        $ship->shouldReceive('getRump')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipRump);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->twice()
            ->andReturn($cloakState);

        $wrapper = $this->mock(ShipWrapperInterface::class);
        $this->shipLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->twice()
            ->andReturn($wrapper);

        $wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->twice()
            ->andReturn(null);

        $fromSignature->shouldReceive('setLocation')
            ->with($currentField)
            ->once();
        $fromSignature->shouldReceive('setUserId')
            ->with($userId)
            ->once();
        $fromSignature->shouldReceive('setShipId')
            ->with($shipId)
            ->once();
        $fromSignature->shouldReceive('setSpacecraftName')
            ->with($shipName)
            ->once();
        $fromSignature->shouldReceive('setRump')
            ->with($shipRump)
            ->once();
        $fromSignature->shouldReceive('setIsCloaked')
            ->with($cloakState)
            ->once();
        $fromSignature->shouldReceive('setToDirection')
            ->with($toDirection)
            ->once();
        $fromSignature->shouldReceive('setTime')
            ->with(Mockery::type('int'))
            ->once();

        $toSignature->shouldReceive('setLocation')
            ->with($nextField)
            ->once();
        $toSignature->shouldReceive('setUserId')
            ->with($userId)
            ->once();
        $toSignature->shouldReceive('setShipId')
            ->with($shipId)
            ->once();
        $toSignature->shouldReceive('setSpacecraftName')
            ->with($shipName)
            ->once();
        $toSignature->shouldReceive('setRump')
            ->with($shipRump)
            ->once();
        $toSignature->shouldReceive('setIsCloaked')
            ->with($cloakState)
            ->once();
        $toSignature->shouldReceive('setFromDirection')
            ->with($fromDirection)
            ->once();
        $toSignature->shouldReceive('setTime')
            ->with(Mockery::type('int'))
            ->once();

        $this->flightSignatureRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($fromSignature);
        $this->flightSignatureRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($toSignature);
        $this->flightSignatureRepository->shouldReceive('save')
            ->with($fromSignature)
            ->once();
        $this->flightSignatureRepository->shouldReceive('save')
            ->with($toSignature)
            ->once();

        $this->subject->createSignatures(
            $ship,
            $toDirection,
            $currentField,
            $nextField
        );
    }

    #[DataProvider('directionDataProvider')]
    public function testCreateSignaturesCreatesForSystemMapFields(
        DirectionEnum $fromDirection,
        DirectionEnum $toDirection
    ): void {
        $ship = $this->mock(Ship::class);
        $currentField = $this->mock(StarSystemMap::class);
        $nextField = $this->mock(StarSystemMap::class);
        $fromSignature = $this->mock(FlightSignature::class);
        $toSignature = $this->mock(FlightSignature::class);
        $shipRump = $this->mock(SpacecraftRump::class);

        $userId = 666;
        $shipId = 42;
        $shipName = 'some-name';
        $cloakState = true;
        $ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->times(4)
            ->andReturn($userId);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->times(4)
            ->andReturn($shipId);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipName);
        $ship->shouldReceive('getRump')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipRump);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->twice()
            ->andReturn($cloakState);

        $wrapper = $this->mock(ShipWrapperInterface::class);
        $this->shipLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->twice()
            ->andReturn($wrapper);

        $wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->twice()
            ->andReturn(null);

        $fromSignature->shouldReceive('setLocation')
            ->with($currentField)
            ->once();
        $fromSignature->shouldReceive('setUserId')
            ->with($userId)
            ->once();
        $fromSignature->shouldReceive('setShipId')
            ->with($shipId)
            ->once();
        $fromSignature->shouldReceive('setSpacecraftName')
            ->with($shipName)
            ->once();
        $fromSignature->shouldReceive('setRump')
            ->with($shipRump)
            ->once();
        $fromSignature->shouldReceive('setIsCloaked')
            ->with($cloakState)
            ->once();
        $fromSignature->shouldReceive('setToDirection')
            ->with($toDirection)
            ->once();
        $fromSignature->shouldReceive('setTime')
            ->with(Mockery::type('int'))
            ->once();

        $toSignature->shouldReceive('setLocation')
            ->with($nextField)
            ->once();
        $toSignature->shouldReceive('setUserId')
            ->with($userId)
            ->once();
        $toSignature->shouldReceive('setShipId')
            ->with($shipId)
            ->once();
        $toSignature->shouldReceive('setSpacecraftName')
            ->with($shipName)
            ->once();
        $toSignature->shouldReceive('setRump')
            ->with($shipRump)
            ->once();
        $toSignature->shouldReceive('setIsCloaked')
            ->with($cloakState)
            ->once();
        $toSignature->shouldReceive('setFromDirection')
            ->with($fromDirection)
            ->once();
        $toSignature->shouldReceive('setTime')
            ->with(Mockery::type('int'))
            ->once();

        $this->flightSignatureRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($fromSignature);
        $this->flightSignatureRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($toSignature);
        $this->flightSignatureRepository->shouldReceive('save')
            ->with($fromSignature)
            ->once();
        $this->flightSignatureRepository->shouldReceive('save')
            ->with($toSignature)
            ->once();

        $this->subject->createSignatures(
            $ship,
            $toDirection,
            $currentField,
            $nextField
        );
    }
}
