<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Mockery;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\StuTestCase;

class GameResponseFinalizerTest extends StuTestCase
{
    public function testFinalizeSetsUpComponentsRendersAndStoresRenderTime(): void
    {
        $twigPage = $this->mock(TwigPageInterface::class);
        $componentSetup = $this->mock(ComponentSetupInterface::class);
        $renderer = $this->mock(GameTwigRendererInterface::class);
        $game = $this->mock(GameControllerInterface::class);
        $gameRequest = $this->mock(GameRequest::class);

        $twigPage->shouldReceive('isTemplateSet')->once()->andReturnTrue();
        $componentSetup->shouldReceive('setup')->with($game)->once();
        $game->shouldReceive('hasUser')->once()->andReturnFalse();
        $renderer->shouldReceive('render')
            ->with($game, null)
            ->once()
            ->andReturn('rendered response');
        $gameRequest->shouldReceive('setRenderMs')
            ->with(Mockery::type('int'))
            ->once();

        $result = (new GameResponseFinalizer($twigPage, $componentSetup, $renderer))
            ->finalize($game, $gameRequest);

        $this->assertSame('rendered response', $result);
    }
}