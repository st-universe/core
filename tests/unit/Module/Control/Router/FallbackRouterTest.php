<?php

declare(strict_types=1);

namespace Stu\Module\Control\Router;

use Mockery\MockInterface;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\Handler\FallbackHandlerInterface;
use Stu\StuTestCase;

class FallbackRouterTest extends StuTestCase
{
    private MockInterface&FallbackHandlerInterface $handler;

    private FallbackRouterInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->handler = $this->mock(FallbackHandlerInterface::class);

        $this->subject = new FallbackRouter([FallbackRouteException::class => $this->handler]);
    }

    public function testShowFallbackSiteExpectExceptionIfUnknownClass(): void
    {
        static::expectException(FallbackRouteException::class);
        static::expectExceptionMessageMatches('/no fallback handler for exception class .*/');

        $exception = new class extends FallbackRouteException {};

        $this->subject->showFallbackSite($exception, $this->mock(GameControllerInterface::class));
    }

    public function testShowFallbackSiteExpectHandlingIfKnownException(): void
    {
        $exception = new FallbackRouteException();
        $game = $this->mock(GameControllerInterface::class);

        $this->handler->shouldReceive('handle')
            ->with($exception, $game)
            ->once();

        $this->subject->showFallbackSite($exception, $game);
    }
}
