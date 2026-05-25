<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Control;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class AlertStateManagerTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftLoaderInterface<SpacecraftWrapperInterface> */
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;

    private MockInterface&SystemActivation $systemActivation;

    private MockInterface&SystemDeactivation $systemDeactivation;

    private MockInterface&GameControllerInterface $game;

    private MockInterface&SpacecraftWrapperInterface $target;

    private AlertStateManagerInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        //injected
        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->systemActivation = $this->mock(SystemActivation::class);
        $this->systemDeactivation = $this->mock(SystemDeactivation::class);
        $this->game = $this->mock(GameControllerInterface::class);

        $this->target = $this->mock(SpacecraftWrapperInterface::class);

        $this->subject = new AlertStateManager(
            $this->spacecraftLoader,
            $this->systemActivation,
            $this->systemDeactivation,
            $this->game,
        );
    }

    /**
     * @return array<array{0: SpacecraftAlertStateEnum, 1: array<SpacecraftSystemTypeEnum>, 2: array<SpacecraftSystemTypeEnum>, 3: string}>
     */
    public static function provideData(): array
    {
        return [
            [SpacecraftAlertStateEnum::ALERT_GREEN, [], [
                SpacecraftSystemTypeEnum::PHASER,
                SpacecraftSystemTypeEnum::TORPEDO,
                SpacecraftSystemTypeEnum::SHIELDS
            ], 'Die Alarmstufe wurde auf [b][color=green]Grün[/color][/b] geändert'],
            [SpacecraftAlertStateEnum::ALERT_YELLOW, [
                SpacecraftSystemTypeEnum::NBS
            ], [], 'Die Alarmstufe wurde auf [b][color=yellow]Gelb[/color][/b] geändert'],
            [SpacecraftAlertStateEnum::ALERT_RED, [
                SpacecraftSystemTypeEnum::SHIELDS,
                SpacecraftSystemTypeEnum::NBS,
                SpacecraftSystemTypeEnum::PHASER,
                SpacecraftSystemTypeEnum::TORPEDO
            ], [], 'Die Alarmstufe wurde auf [b][color=red]Rot[/color][/b] geändert'],
        ];
    }

    /**
     * @param array<SpacecraftSystemTypeEnum> $expectedActivations
     * @param array<SpacecraftSystemTypeEnum> $expectedDeactivations
     */
    #[DataProvider('provideData')]
    public function testSetAlertState(
        SpacecraftAlertStateEnum $alertState,
        array $expectedActivations,
        array $expectedDeactivations,
        string $expectedInfo
    ): void {
        $spacecraft = $this->mock(Spacecraft::class);
        $user = $this->mock(User::class);
        $info = $this->mock(InformationWrapper::class);

        $this->game->shouldReceive('hasUser')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->target->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($spacecraft);
        $this->target->shouldReceive('setAlertState')
            ->with($alertState)
            ->once()
            ->andReturn(null);

        $spacecraft->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $spacecraft->shouldReceive('isConstruction')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $spacecraft->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $spacecraft->shouldReceive('isCloaked')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturnFalse();

        foreach ($expectedActivations as $expectedActivation) {
            $this->systemActivation->shouldReceive('activateIntern')
                ->with($this->target, $expectedActivation, $info, false)
                ->once();
        }
        foreach ($expectedDeactivations as $expectedDectivation) {
            $spacecraft->shouldReceive('hasSpacecraftSystem')
                ->with($expectedDectivation)
                ->once()
                ->andReturnTrue();
            $this->systemDeactivation->shouldReceive('deactivateIntern')
                ->with($this->target, $expectedDectivation, $info)
                ->once();
        }

        $info->shouldReceive('addInformation')
            ->with($expectedInfo)
            ->once();

        $this->subject->setAlertState(
            $this->target,
            $alertState
        );
    }

    public function testSetAlertStateDoesNotExposeForeignSpacecraftInformation(): void
    {
        $spacecraft = $this->mock(Spacecraft::class);
        $user = $this->mock(User::class);

        $this->game->shouldReceive('hasUser')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->never();
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->target->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($spacecraft);
        $this->target->shouldReceive('setAlertState')
            ->with(SpacecraftAlertStateEnum::ALERT_RED)
            ->once()
            ->andReturn(null);

        $spacecraft->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $spacecraft->shouldReceive('isConstruction')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $spacecraft->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $spacecraft->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        foreach ([
            SpacecraftSystemTypeEnum::SHIELDS,
            SpacecraftSystemTypeEnum::NBS,
            SpacecraftSystemTypeEnum::PHASER,
            SpacecraftSystemTypeEnum::TORPEDO
        ] as $expectedActivation) {
            $this->systemActivation->shouldReceive('activateIntern')
                ->with($this->target, $expectedActivation, \Mockery::type(InformationWrapper::class), false)
                ->once();
        }

        $this->subject->setAlertState(
            $this->target,
            SpacecraftAlertStateEnum::ALERT_RED
        );
    }
}
