<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Mockery\MockInterface;
use request;
use Stu\Component\Game\GameStateEnum;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameStateInterface;
use Stu\Orm\Entity\GameConfig;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\StuTestCase;

class SetGameStateTest extends StuTestCase
{
    private MockInterface&GameConfigRepositoryInterface $gameConfigRepository;
    private MockInterface&ActionControllerContext $context;

    private SetGameState $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->gameConfigRepository = $this->mock(GameConfigRepositoryInterface::class);
        $this->context = $this->mock(ActionControllerContext::class);

        $this->subject = new SetGameState(
            $this->gameConfigRepository
        );
    }

    public function testHandleRejectsInvalidGameState(): void
    {
        request::setMockVars(['game_state' => 999]);

        $this->context->shouldReceive('setView')
            ->with(ShowScripts::VIEW_IDENTIFIER)
            ->once();
        $this->context->shouldReceive('getInfo->addInformation')
            ->with('Ungültiger Spielmodus')
            ->once();

        $this->subject->handle($this->context);
    }

    public function testHandleRejectsMissingGameStateConfig(): void
    {
        request::setMockVars(['game_state' => GameStateEnum::MAINTENANCE->value]);

        $this->context->shouldReceive('setView')
            ->with(ShowScripts::VIEW_IDENTIFIER)
            ->once();
        $this->gameConfigRepository->shouldReceive('getByOption')
            ->with(GameStateInterface::CONFIG_GAMESTATE)
            ->once()
            ->andReturn(null);
        $this->context->shouldReceive('getInfo->addInformation')
            ->with('Spielmodus-Konfiguration nicht gefunden')
            ->once();

        $this->subject->handle($this->context);
    }

    public function testHandleUpdatesGameState(): void
    {
        request::setMockVars(['game_state' => GameStateEnum::RELOCATION->value]);

        $contextConfig = new GameConfig();

        $this->context->shouldReceive('setView')
            ->with(ShowScripts::VIEW_IDENTIFIER)
            ->once();
        $this->gameConfigRepository->shouldReceive('getByOption')
            ->with(GameStateInterface::CONFIG_GAMESTATE)
            ->once()
            ->andReturn($contextConfig);
        $this->gameConfigRepository->shouldReceive('save')
            ->with($contextConfig)
            ->once();
        $this->context->shouldReceive('getInfo->addInformation')
            ->with('Der Spielmodus wurde auf "Umzug" gesetzt')
            ->once();

        $this->subject->handle($this->context);

        self::assertSame(GameStateEnum::RELOCATION->value, $contextConfig->getValue());
    }

    public function testPerformSessionCheckReturnsTrue(): void
    {
        self::assertTrue($this->subject->performSessionCheck());
    }
}
