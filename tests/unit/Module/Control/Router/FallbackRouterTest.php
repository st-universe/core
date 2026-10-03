<?php

declare(strict_types=1);

namespace Stu\Module\Control\Router;

use Mockery\MockInterface;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\Component\View\ViewContext;
use Stu\Module\Control\Component\View\ViewContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\Handler\FallbackHandlerInterface;
use Stu\StuTestCase;

class FallbackRouterTest extends StuTestCase
{
    private MockInterface&ViewContextFactoryInterface $viewContextFactory;
    private MockInterface&FallbackHandlerInterface $handler;

    private FallbackRouterInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->viewContextFactory = $this->mock(ViewContextFactoryInterface::class);
        $this->handler = $this->mock(FallbackHandlerInterface::class);

        $this->subject = new FallbackRouter(
            $this->viewContextFactory,
            [FallbackRouteException::class => $this->handler]
        );
    }

    public function testShowFallbackSiteExpectExceptionIfUnknownClass(): void
    {
        static::expectException(FallbackRouteException::class);
        static::expectExceptionMessageMatches('/no fallback handler for exception class .*/');

        $exception = new class () extends FallbackRouteException {};

        $this->subject->showFallbackSite($exception, $this->mock(GameControllerInterface::class));
    }

    public function testShowFallbackSiteExpectHandlingIfKnownException(): void
    {
        $exception = new FallbackRouteException();
        $game = $this->mock(GameControllerInterface::class);
        $context = $this->mock(ViewContext::class);

        $this->viewContextFactory->shouldReceive('createViewContext')
            ->with($game, ModuleEnum::GAME)
            ->once()
            ->andReturn($context);

        $this->handler->shouldReceive('handle')
            ->with($exception, $context)
            ->once();

        $this->subject->showFallbackSite($exception, $game);
    }
}
