<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\Fragments\RenderFragmentInterface;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class GameTalRendererTest extends StuTestCase
{
    /** @var MockInterface&ConfigInterface */
    private MockInterface $config;

    /** @var MockInterface&Fragments\RenderFragmentInterface */
    private MockInterface $renderFragment;

    private GameTalRenderer $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);
        $this->renderFragment = $this->mock(RenderFragmentInterface::class);

        $this->subject = new GameTalRenderer(
            $this->config,
            [$this->renderFragment]
        );
    }

    public function testRenderRenders(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $talPage = $this->mock(TalPageInterface::class);
        $user = $this->mock(UserInterface::class);

        $output = 'some-render-output';
        $configValueVersion = 'some-version';
        $configValueWiki = 'some-wiki';
        $configValueForum = 'some-forum';
        $configValueChat = 'some-chat';
        $configValueUserAvatarPath = 'some-user-path';
        $configValueAllianceAvatarPath = 'some-avatar-path';

        $this->renderFragment->shouldReceive('render')
            ->with($user, $talPage, $game)
            ->once();

        $game->shouldReceive('getExecuteJS')
            ->with(GameEnum::JS_EXECUTION_BEFORE_RENDER)
            ->once()
            ->andReturn(['JAVASCRIPT']);

        $talPage->shouldReceive('parse')
            ->withNoArgs()
            ->once()
            ->andReturn($output);
        $talPage->shouldReceive('setVar')
            ->with('THIS', $game)
            ->once();
        $talPage->shouldReceive('setVar')
            ->with('EXECUTEJSBEFORERENDER', ['JAVASCRIPT'])
            ->once();
        $talPage->shouldReceive('setVar')
            ->with('USER', $user)
            ->once();
        $talPage->shouldReceive('setVar')
            ->with('GAME_VERSION', $configValueVersion)
            ->once();
        $talPage->shouldReceive('setVar')
            ->with('WIKI', $configValueWiki)
            ->once();
        $talPage->shouldReceive('setVar')
            ->with('FORUM', $configValueForum)
            ->once();
        $talPage->shouldReceive('setVar')
            ->with('CHAT', $configValueChat)
            ->once();
        $talPage->shouldReceive('setVar')
            ->with(
                'ASSET_PATHS',
                [
                    'alliance' => $configValueAllianceAvatarPath,
                    'user' => $configValueUserAvatarPath,
                    'faction' => 'assets/rassen/',
                ]
            )
            ->once();

        $this->config->shouldReceive('get')
            ->with('game.version')
            ->once()
            ->andReturn($configValueVersion);
        $this->config->shouldReceive('get')
            ->with('wiki.base_url')
            ->once()
            ->andReturn($configValueWiki);
        $this->config->shouldReceive('get')
            ->with('board.base_url')
            ->once()
            ->andReturn($configValueForum);
        $this->config->shouldReceive('get')
            ->with('discord.url')
            ->once()
            ->andReturn($configValueChat);
        $this->config->shouldReceive('get')
            ->with('game.alliance_avatar_path')
            ->once()
            ->andReturn($configValueAllianceAvatarPath);
        $this->config->shouldReceive('get')
            ->with('game.user_avatar_path')
            ->once()
            ->andReturn($configValueUserAvatarPath);

        static::assertSame(
            $output,
            $this->subject->render($game, $user, $talPage)
        );
    }
}
