<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowInnerContent;

use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Control\ViewWithTutorialInterface;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;

final class ShowInnerContent implements ViewControllerInterface, ViewWithTutorialInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_INNER_CONTENT';

    public function __construct(private ViewComponentLoaderInterface $viewComponentLoader) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        /** @var ModuleEnum  */
        $view = $game->getViewContextMetadata(ViewContextMetadataTypeEnum::MODULE_VIEW);

        $this->viewComponentLoader->registerViewComponents($view, $game);
        $game->setTemplateVar('VIEW_TEMPLATE', $view->getTemplate());

        $game->showMacro('html/view/breadcrumbAndView.twig');
    }
}
