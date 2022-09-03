<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildResult;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowBuildResult implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILD_RESULT';

    private ColonyLoaderInterface $colonyLoader;

    private ShowBuildResultRequestInterface $showBuildResultRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowBuildResultRequestInterface $showBuildResultRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showBuildResultRequest = $showBuildResultRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBuildResultRequest->getColonyId(),
            $userId
        );
        $game->showMacro('html/sitemacros.xhtml/systeminformation');

        $game->setTemplateVar('COLONY', $colony);
        $game->addExecuteJS('refreshColony();');
    }
}
