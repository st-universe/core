<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\SwitchView;

use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Module\Game\View\ShowInnerContent\ShowInnerContent;

final class SwitchView implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SWITCH_VIEW';

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $moduleView = ModuleEnum::from(request::getStringFatal('view'));

        $context->setView(ShowInnerContent::VIEW_IDENTIFIER);
        $context->setViewContext(ViewContextMetadataTypeEnum::MODULE_VIEW, $moduleView);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
