<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use request;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\StuTestCase;

class GameSetupTest extends StuTestCase
{
    public function testSetTemplateAndComponentsUsesSwitchTemplate(): void
    {
        request::setMockVars(['switch' => '1']);

        $componentRegistration = $this->mock(ComponentRegistrationInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $game->shouldReceive('setTemplateFile')
            ->with('html/view/breadcrumbAndView.twig')
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('VIEW_TEMPLATE', 'view-template')
            ->once();
        $componentRegistration->shouldNotReceive('registerComponent');

        (new GameSetup($componentRegistration))
            ->setTemplateAndComponents('view-template', $game);
    }

    public function testSetTemplateAndComponentsUsesGameTemplateAndRegistersComponents(): void
    {
        request::setMockVars([]);

        $componentRegistration = $this->mock(ComponentRegistrationInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $game->shouldReceive('setTemplateVar')
            ->with('VIEW_TEMPLATE', 'view-template')
            ->once();
        $game->shouldReceive('setTemplateFile')
            ->with('html/game/game.twig')
            ->once();

        foreach (GameComponentEnum::cases() as $component) {
            $componentRegistration->shouldReceive('registerComponent')
                ->with($component)
                ->once()
                ->andReturnSelf();
        }

        foreach ([
            [GameComponentEnum::PM],
            [GameComponentEnum::SERVERTIME_AND_VERSION],
        ] as [$component]) {
            $componentRegistration->shouldReceive('addComponentUpdate')
                ->with($component, null, false)
                ->once()
                ->andReturnSelf();
        }

        (new GameSetup($componentRegistration))
            ->setTemplateAndComponents('view-template', $game);
    }
}
