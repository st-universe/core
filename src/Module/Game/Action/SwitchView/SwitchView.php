<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\SwitchView;

use request;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\View\ShowInnerContent\ShowInnerContent;

final class SwitchView implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SWITCH_VIEW';

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(
            ShowInnerContent::VIEW_IDENTIFIER,
            ['VIEW' => ModuleViewEnum::from(request::getStringFatal('view'))]
        );
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
