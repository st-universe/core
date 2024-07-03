<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\Component;

use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\Fragments\RenderFragmentInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ComponentLoaderTest extends StuTestCase
{
    /** @var MockInterface&RenderFragmentInterface  */
    private MockInterface $componentProvider;

    /** @var MockInterface&GameControllerInterface  */
    private MockInterface $game;

    private ComponentLoaderInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->componentProvider = $this->mock(RenderFragmentInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new ComponentLoader([ComponentEnum::PM_NAVLET->value => $this->componentProvider]);
    }

    public function testLoadComponentUpdatesAsInstantUpdate(): void
    {
        $this->subject->addComponentUpdate(ComponentEnum::USER_NAVLET);

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
        $this->subject->addComponentUpdate(ComponentEnum::USER_NAVLET, false);
        $this->subject->addComponentUpdate(ComponentEnum::USER_NAVLET, false);

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
        $this->subject->addComponentUpdate(ComponentEnum::PM_NAVLET, false);

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
        $this->subject->addComponentUpdate(ComponentEnum::PM_NAVLET);

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

        $twigPage = $this->mock(TwigPageInterface::class);

        $this->subject->registerComponent(ComponentEnum::SERVERTIME_NAVLET);

        $this->subject->loadRegisteredComponents($twigPage, $this->game);
    }

    public function testLoadRegisteredComponents(): void
    {
        $twigPage = $this->mock(TwigPageInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->subject->registerComponent(ComponentEnum::PM_NAVLET);
        $this->subject->registerComponent(ComponentEnum::PM_NAVLET);

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->componentProvider->shouldReceive('render')
            ->with($user, $twigPage, $this->game)
            ->once();

        $this->subject->loadRegisteredComponents($twigPage, $this->game);
    }
}
