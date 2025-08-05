<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Game\GameStateEnum;
use Stu\Exception\MaintenanceGameStateException;
use Stu\Exception\RelocationGameStateException;
use Stu\Exception\ResetGameStateException;
use Stu\Orm\Entity\GameConfig;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\StuTestCase;

class GameStateTest extends StuTestCase
{
    private MockInterface&GameConfigRepositoryInterface $gameConfigRepository;

    private GameStateInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->gameConfigRepository = $this->mock(GameConfigRepositoryInterface::class);

        $this->subject = new GameState(
            $this->gameConfigRepository
        );
    }

    public static function provideCheckGameStateData(): array
    {
        return [
            [GameStateEnum::MAINTENANCE, false, MaintenanceGameStateException::class],
            [GameStateEnum::MAINTENANCE, true, null],
            [GameStateEnum::ONLINE, false, null],
            [GameStateEnum::ONLINE, true, null],
            [GameStateEnum::TICK, false, null],
            [GameStateEnum::TICK, true, null],
            [GameStateEnum::RELOCATION, false, RelocationGameStateException::class],
            [GameStateEnum::RELOCATION, true, RelocationGameStateException::class],
            [GameStateEnum::RESET, false, ResetGameStateException::class],
            [GameStateEnum::RESET, true, ResetGameStateException::class],
        ];
    }

    /**
     * @param null|class-string $expectedExceptionClass
     */
    #[DataProvider('provideCheckGameStateData')]
    public function testCheckGameState(
        GameStateEnum $gameState,
        bool $isAdmin,
        ?string $expectedExceptionClass
    ): void {
        $gameConfig = $this->mock(GameConfig::class);

        if ($expectedExceptionClass !== null) {
            static::expectException($expectedExceptionClass);
        }

        $gameConfig->shouldReceive('getOption')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $gameConfig->shouldReceive('getValue')
            ->withNoArgs()
            ->once()
            ->andReturn($gameState->value);

        $this->gameConfigRepository->shouldReceive('findAll')
            ->withNoArgs()
            ->once()
            ->andReturn([$gameConfig]);

        $this->subject->checkGameState($isAdmin);
    }
}
