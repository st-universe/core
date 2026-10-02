<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\Overview;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Control\ViewWithTutorialInterface;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;

final class Overview implements ViewControllerInterface, ViewWithTutorialInterface
{
    public const string VIEW_IDENTIFIER = 'OVERVIEW';

    public function __construct(
        private readonly ViewComponentLoaderInterface $viewComponentLoader
    ) {}

    #[\Override]
    public function handle(ViewControllerContext $context): void
    {
        $module = $context->getModule();
        $this->viewComponentLoader->registerViewComponents($module, $context);

        $context->setPageTitle($module->getTitle());
        $context->setViewTemplate($module->getTemplate());
    }
}
