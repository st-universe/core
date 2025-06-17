<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class DriveActivationConsequenceTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftSystemManagerInterface */
    private $spacecraftSystemManager;
    /** @var MockInterface&MessageFactoryInterface */
    private $messageFactory;

    private FlightConsequenceInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    #[Override]
    protected function setUp(): void
    {
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new DriveActivationConsequence(
            $this->spacecraftSystemManager,
            $this->messageFactory
        );
    }

    public function testTriggerExpectNothingWhenShipDestroyed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectNothingWhenShipTractored(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public static function provideTriggerData(): array
    {
        return [
            [true, false, false, false, SpacecraftSystemTypeEnum::IMPULSEDRIVE],
            [true, false, false, true, SpacecraftSystemTypeEnum::IMPULSEDRIVE],
            [false, true, false, false, SpacecraftSystemTypeEnum::WARPDRIVE],
            [false, true, false, true, SpacecraftSystemTypeEnum::WARPDRIVE],
            [false, false, true, false, SpacecraftSystemTypeEnum::TRANSWARP_COIL],
            [false, false, true, true, SpacecraftSystemTypeEnum::TRANSWARP_COIL],
        ];
    }

    #[DataProvider('provideTriggerData')]
    public function testTrigger(
        bool $isImpulsNeeded,
        bool $isWarpdriveNeeded,
        bool $isTranswarpNeeded,
        bool $currentSystemState,
        SpacecraftSystemTypeEnum $expectedSystemId
    ): void {
        $messages = $this->mock(MessageCollectionInterface::class);
        $message = $this->mock(MessageInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getSystemState')
            ->with($expectedSystemId)
            ->once()
            ->andReturn($currentSystemState);
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(123);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');

        $this->flightRoute->shouldReceive('isImpulseDriveNeeded')
            ->withNoArgs()
            ->once()
            ->andReturn($isImpulsNeeded);
        $this->flightRoute->shouldReceive('isWarpDriveNeeded')
            ->withNoArgs()
            ->once()
            ->andReturn($isWarpdriveNeeded);
        $this->flightRoute->shouldReceive('isTranswarpCoilNeeded')
            ->withNoArgs()
            ->once()
            ->andReturn($isTranswarpNeeded);

        if (!$currentSystemState) {
            $this->spacecraftSystemManager->shouldReceive('activate')
                ->with($this->wrapper, $expectedSystemId)
                ->once();
        }

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->withNoArgs()
            ->once()
            ->andReturn($message);

        if (!$currentSystemState) {
            $message->shouldReceive('add')
                ->with(sprintf(
                    'Die SHIP aktiviert %s %s',
                    $expectedSystemId === SpacecraftSystemTypeEnum::TRANSWARP_COIL ? 'die' : 'den',
                    $expectedSystemId->getDescription()
                ))
                ->once();
        }

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }
}
