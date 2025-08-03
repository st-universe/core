<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Control;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\Control\SystemActivation;
use Stu\Component\Spacecraft\System\Control\SystemDeactivation;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class AlertStateManagerTest extends StuTestCase
{
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;
    private MockInterface&SystemActivation $systemActivation;
    private MockInterface&SystemDeactivation $systemDeactivation;
    private MockInterface&GameControllerInterface $game;

    private MockInterface&SpacecraftWrapperInterface $target;

    private AlertStateManagerInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->systemActivation = $this->mock(SystemActivation::class);
        $this->systemDeactivation = $this->mock(SystemDeactivation::class);
        $this->game = $this->mock(GameControllerInterface::class);

        $this->target = $this->mock(SpacecraftWrapperInterface::class);

        $this->subject = new AlertStateManager(
            $this->spacecraftLoader,
            $this->spacecraftRepository,
            $this->systemActivation,
            $this->systemDeactivation,
            $this->game,
        );
    }

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

    #[DataProvider('provideData')]
    public function testSetAlertState(
        SpacecraftAlertStateEnum $alertState,
        array $expectedActivations,
        array $expectedDeactivations,
        string $expectedInfo
    ): void {
        $spacecraft = $this->mock(Spacecraft::class);

        $this->target->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($spacecraft);
        $this->target->shouldReceive('setAlertState')
            ->with($alertState)
            ->once()
            ->andReturn(null);

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
                ->with($this->target, $expectedActivation, $this->game, false)
                ->once();
        }
        foreach ($expectedDeactivations as $expectedDectivation) {
            $spacecraft->shouldReceive('hasSpacecraftSystem')
                ->with($expectedDectivation)
                ->once()
                ->andReturnTrue();
            $this->systemDeactivation->shouldReceive('deactivateIntern')
                ->with($this->target, $expectedDectivation, $this->game)
                ->once();
        }

        $this->spacecraftRepository->shouldReceive('save')
            ->with($spacecraft)
            ->times(2);

        $this->game->shouldReceive('addInformation')
            ->with($expectedInfo)
            ->once();

        $this->subject->setAlertState(
            $this->target,
            $alertState
        );
    }
}
