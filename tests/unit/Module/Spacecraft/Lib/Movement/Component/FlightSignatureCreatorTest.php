<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Map\DirectionEnum;
use Stu\Orm\Entity\FlightSignatureInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\StuTestCase;

class FlightSignatureCreatorTest extends StuTestCase
{
    private MockInterface&FlightSignatureRepositoryInterface $flightSignatureRepository;

    private FlightSignatureCreator $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->flightSignatureRepository = $this->mock(FlightSignatureRepositoryInterface::class);

        $this->subject = new FlightSignatureCreator(
            $this->flightSignatureRepository
        );
    }

    public function testCreateSignaturesExpectExceptionWhenDifferentType1(): void
    {
        static::expectExceptionMessage('wayopints have different type');
        static::expectException(InvalidArgumentException::class);

        $this->subject->createSignatures(
            $this->mock(ShipInterface::class),
            DirectionEnum::LEFT,
            $this->mock(MapInterface::class),
            $this->mock(StarSystemMapInterface::class)
        );
    }

    public function testCreateSignaturesExpectExceptionWhenDifferentType2(): void
    {
        static::expectExceptionMessage('wayopints have different type');
        static::expectException(InvalidArgumentException::class);

        $this->subject->createSignatures(
            $this->mock(ShipInterface::class),
            DirectionEnum::LEFT,
            $this->mock(StarSystemMapInterface::class),
            $this->mock(MapInterface::class)
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
        $ship = $this->mock(ShipInterface::class);
        $currentField = $this->mock(MapInterface::class);
        $nextField = $this->mock(MapInterface::class);
        $fromSignature = $this->mock(FlightSignatureInterface::class);
        $toSignature = $this->mock(FlightSignatureInterface::class);
        $shipRump = $this->mock(SpacecraftRumpInterface::class);

        $userId = 666;
        $shipId = 42;
        $shipName = 'some-name';
        $cloakState = true;

        $ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($userId);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipId);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipName);
        $ship->shouldReceive('getRump')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipRump);
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->twice()
            ->andReturn($cloakState);

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
        $ship = $this->mock(ShipInterface::class);
        $currentField = $this->mock(StarSystemMapInterface::class);
        $nextField = $this->mock(StarSystemMapInterface::class);
        $fromSignature = $this->mock(FlightSignatureInterface::class);
        $toSignature = $this->mock(FlightSignatureInterface::class);
        $shipRump = $this->mock(SpacecraftRumpInterface::class);

        $userId = 666;
        $shipId = 42;
        $shipName = 'some-name';
        $cloakState = true;

        $ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($userId);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipId);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipName);
        $ship->shouldReceive('getRump')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipRump);
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->twice()
            ->andReturn($cloakState);

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
