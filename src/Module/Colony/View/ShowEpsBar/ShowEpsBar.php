<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowEpsBar;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowEpsBar implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_EPSBAR_AJAX';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showEpsBarRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowEpsBarRequestInterface $showEpsBarRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showEpsBarRequest = $showEpsBarRequest;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showEpsBarRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $game->showMacro('html/colonymacros.xhtml/colonyeps');
        $game->setTemplateVar('COLONY', $colony);
    }
}
