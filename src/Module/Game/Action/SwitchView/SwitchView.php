<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\SwitchView;

use request;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Game\View\ShowInnerContent\ShowInnerContent;

final class SwitchView implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SWITCH_VIEW';

    public function handle(GameControllerInterface $game): void
    {
        $moduleView = ModuleViewEnum::from(request::getStringFatal('view'));

        $game->setView(ShowInnerContent::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::VIEW, $moduleView);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
