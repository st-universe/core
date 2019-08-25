<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildResult;

use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowBuildResult implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILD_RESULT';

    private $colonyLoader;

    private $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setTemplateVar('COLONY', $colony);
        $game->addExecuteJS('refreshColony');
        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/sitemacros.xhtml/systeminformation');
    }
}
