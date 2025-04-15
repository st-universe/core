<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\SwitchView;

use Override;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Game\View\ShowInnerContent\ShowInnerContent;

final class SwitchView implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SWITCH_VIEW';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $moduleView = ModuleEnum::from(request::getStringFatal('view'));

        $game->setView(ShowInnerContent::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::MODULE_VIEW, $moduleView);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
