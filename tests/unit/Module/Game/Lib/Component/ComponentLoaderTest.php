<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\Component;

use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\Fragments\RenderFragmentInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ComponentLoaderTest extends StuTestCase
{
    /** @var MockInterface&RenderFragmentInterface  */
    private $componentProvider;
    /** @var MockInterface&ComponentRendererInterface  */
    private $componentRenderer;

    /** @var MockInterface&GameControllerInterface  */
    private $game;

    private ComponentLoaderInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->componentProvider = $this->mock(RenderFragmentInterface::class);
        $this->componentRenderer = $this->mock(ComponentRendererInterface::class);

        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new ComponentLoader(
            $this->componentRenderer,
            [ComponentEnum::PM->value => $this->componentProvider]
        );
    }

    public function testLoadComponentUpdatesAsInstantUpdate(): void
    {
        $this->subject->addComponentUpdate(ComponentEnum::USER);

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('navlet_user', '/game.php?SHOW_COMPONENT=1&component=user');",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadComponentUpdatesWithoutRefreshInterval(): void
    {
        $this->subject->addComponentUpdate(ComponentEnum::USER, false);
        $this->subject->addComponentUpdate(ComponentEnum::USER, false);

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('navlet_user', '/game.php?SHOW_COMPONENT=1&component=user');",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadComponentUpdatesWithRefreshInterval(): void
    {
        $this->subject->addComponentUpdate(ComponentEnum::PM, false);

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('navlet_pm', '/game.php?SHOW_COMPONENT=1&component=pm', 60000);",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadComponentUpdatesWithInstantAndRefreshInterval(): void
    {
        $this->subject->addComponentUpdate(ComponentEnum::PM);

        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('navlet_pm', '/game.php?SHOW_COMPONENT=1&component=pm');",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();
        $this->game->shouldReceive('addExecuteJS')
            ->with(
                "updateComponent('navlet_pm', '/game.php?SHOW_COMPONENT=1&component=pm', 60000);",
                GameEnum::JS_EXECUTION_AFTER_RENDER
            )
            ->once();

        $this->subject->loadComponentUpdates($this->game);
    }

    public function testLoadRegisteredComponentsExpectExceptionIfNoProviderAvailable(): void
    {
        static::expectExceptionMessage('componentProvider with follwing id does not exist: servertime');
        static::expectException(RuntimeException::class);

        $this->subject->registerComponent(ComponentEnum::SERVERTIME_AND_VERSION);

        $this->subject->loadRegisteredComponents($this->game);
    }

    public function testLoadRegisteredComponents(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->subject->registerComponent(ComponentEnum::PM);
        $this->subject->registerComponent(ComponentEnum::PM);

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->componentRenderer->shouldReceive('renderComponent')
            ->with($this->componentProvider, $user, $this->game)
            ->once();

        $this->subject->loadRegisteredComponents($this->game);
    }
}
