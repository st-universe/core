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

class DriveDeactivationConsequenceTest extends StuTestCase
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

        $this->subject = new DriveDeactivationConsequence(
            $this->spacecraftSystemManager,
            $this->messageFactory
        );
    }

    public function testTriggerExpectNothingWhenShipDestroyed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
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

        $this->ship->shouldReceive('getCondition->isDestroyed')
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
            [true, false, SpacecraftSystemTypeEnum::WARPDRIVE, false],
            [true, false, SpacecraftSystemTypeEnum::WARPDRIVE, true, false],
            [true, false, SpacecraftSystemTypeEnum::WARPDRIVE, true, true],
            [false, true, SpacecraftSystemTypeEnum::IMPULSEDRIVE, false],
            [false, true, SpacecraftSystemTypeEnum::IMPULSEDRIVE, true, false],
            [false, true, SpacecraftSystemTypeEnum::IMPULSEDRIVE, true, true],
        ];
    }

    #[DataProvider('provideTriggerData')]
    public function testTrigger(
        bool $isImpulsNeeded,
        bool $isWarpdriveNeeded,
        SpacecraftSystemTypeEnum $systemId,
        bool $hasSpacecraftSystem,
        ?bool $isSystemActive = null,
    ): void {
        $messages = $this->mock(MessageCollectionInterface::class);
        $message = $this->mock(MessageInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(123);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('hasSpacecraftSystem')
            ->with($systemId)
            ->andReturn($hasSpacecraftSystem);

        $this->flightRoute->shouldReceive('isImpulseDriveNeeded')
            ->withNoArgs()
            ->once()
            ->andReturn($isImpulsNeeded);
        $this->flightRoute->shouldReceive('isWarpDriveNeeded')
            ->withNoArgs()
            ->once()
            ->andReturn($isWarpdriveNeeded);

        if ($hasSpacecraftSystem) {
            $this->ship->shouldReceive('getSystemState')
                ->with($systemId)
                ->andReturn($isSystemActive);
        }

        if ($hasSpacecraftSystem && $isSystemActive) {
            $this->spacecraftSystemManager->shouldReceive('deactivate')
                ->with($this->wrapper, $systemId, true)
                ->once();

            $messages->shouldReceive('add')
                ->with($message)
                ->once();

            $this->messageFactory->shouldReceive('createMessage')
                ->withNoArgs()
                ->once()
                ->andReturn($message);

            $message->shouldReceive('add')
                ->with(sprintf(
                    'Die SHIP deaktiviert %s %s',
                    $systemId === SpacecraftSystemTypeEnum::TRANSWARP_COIL ? 'die' : 'den',
                    $systemId->getDescription()
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
