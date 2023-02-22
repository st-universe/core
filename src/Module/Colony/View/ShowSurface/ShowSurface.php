<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSurface;

use request;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowSurface implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONY_SURFACE';

    private ColonyLoaderInterface $colonyLoader;

    private ShowSurfaceRequestInterface $showSurfaceRequest;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowSurfaceRequestInterface $showSurfaceRequest,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showSurfaceRequest = $showSurfaceRequest;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showSurfaceRequest->getColonyId(),
            $userId,
            false
        );

        $game->setTemplateVar('COLONY', $colony);
        $game->showMacro('html/colonymacros.xhtml/colonysurface');
        $game->setTemplateVar(
            'COLONY_SURFACE',
            $this->colonyLibFactory->createColonySurface($colony, request::getInt('bid') !== 0 ? request::getInt('bid') : null)
        );
    }
}
