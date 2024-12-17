<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\StuTestCase;

class ComponentLoaderTest extends StuTestCase
{
    /** @var MockInterface&ComponentRegistrationInterface  */
    private $componentRegistration;
    /** @var MockInterface&ComponentRendererInterface  */
    private $componentRenderer;

    /** @var MockInterface&GameControllerInterface  */
    private $game;

    private ComponentLoaderInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->componentRegistration = $this->mock(ComponentRegistrationInterface::class);
        $this->componentRenderer = $this->mock(ComponentRendererInterface::class);

        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new ComponentLoader(
            $this->componentRegistration,
            $this->componentRenderer
        );
    }

    public function testLoadComponentUpdatesAsInstantUpdate(): void
    {
        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(GameComponentEnum::USER, true)]));

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&id=ID');",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadComponentUpdatesWithoutRefreshInterval(): void
    {
        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(GameComponentEnum::USER, false)]));

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&id=ID');",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadComponentUpdatesWithRefreshInterval(): void
    {
        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(GameComponentEnum::PM, false)]));

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&id=ID', 60000);",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadComponentUpdatesWithInstantAndRefreshInterval(): void
    {
        $this->componentRegistration->shouldReceive('getComponentUpdates')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => new ComponentUpdate(GameComponentEnum::PM, true)]));

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&id=ID');",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();
        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('ID', '/game.php?SHOW_COMPONENT=1&id=ID', 60000);",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadRegisteredComponents(): void
    {
        $this->componentRegistration->shouldReceive('getRegisteredComponents')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection(['ID' => GameComponentEnum::PM]));

        $this->componentRenderer->shouldReceive('renderComponent')
            ->with(Mockery::any(), $this->game)
            ->once();

        $this->game->shouldReceive('setTemplateVar')
            ->with('ID', [
                'id' => 'ID',
                'template' => 'html/game/component/pmComponent.twig'
            ])
            ->once();

        $this->subject->loadRegisteredComponents($this->game);
    }
}
