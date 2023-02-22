<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowEpsBar;

use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowEpsBar implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_EPSBAR_AJAX';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowEpsBarRequestInterface $showEpsBarRequest;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowEpsBarRequestInterface $showEpsBarRequest,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showEpsBarRequest = $showEpsBarRequest;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->colonyLibFactory = $colonyLibFactory;
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
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony));
    }
}
